<?php

namespace App\Services\Game;

use App\Models\User;
use App\Models\GameRoom;
use App\Enums\CardSuit;
use App\Enums\CardRank;
use App\DTOs\CardData;
use App\DTOs\BlotGameStateData;
use App\Exceptions\InvalidGameActionException;
use App\Events\PlayerTurn;
use App\Events\GameStateUpdated;
use App\Jobs\StartNewBlotHand;
use App\Repositories\Eloquent\BlotGameStateRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class BlotGameService
{
    /**
     * @var BlotGameStateRepository
     */
    protected $blotGameStateRepository;

    /**
     * BlotGameService constructor.
     *
     * @param BlotGameStateRepository $blotGameStateRepository
     */
    public function __construct(BlotGameStateRepository $blotGameStateRepository)
    {
        $this->blotGameStateRepository = $blotGameStateRepository;
    }

    /**
     * Start a new hand of Blot
     *
     * @param GameRoom $room
     * @return void
     */
    public function startNewHand(GameRoom $room): void
    {
        // Get players in the room
        $players = $room->players()->where('role', 'player')->get();
        
        if ($players->count() != 2) {
            throw new \Exception('Blot requires exactly 2 players');
        }
        
        // Determine dealer (alternates each hand)
        $metaKey = "blot_meta_{$room->id}";
        $metaState = Cache::get($metaKey, ['dealer_id' => $players->first()->id]);
        $dealerId = $metaState['dealer_id'];
        
        // Alternate dealer for next hand
        $nextDealerId = $players->firstWhere('id', '!=', $dealerId)?->id ?: $players->first()->id;
        $metaState['dealer_id'] = $nextDealerId;
        Cache::put($metaKey, $metaState, now()->addHours(1));
        
        // Create and shuffle 24-card deck (9 to Ace)
        $deck = $this->createBlotDeck();
        shuffle($deck);
        
        // Deal 6 cards to each player
        $hands = [];
        foreach ($players as $player) {
            $hands[$player->id] = array_splice($deck, 0, 6);
        }
        
        // Turn up one card as proposed trump
        $proposedTrump = array_pop($deck);
        
        // Create initial game state
        $gameState = new BlotGameStateData(
            deck: $deck,
            trumpSuit: null, // Not yet selected
            dealerId: $dealerId,
            currentPlayerId: $dealerId, // Dealer selects trump first
            hands: $hands,
            trick: [],
            scores: ['user_' . $players[0]->id => 0, 'user_' . $players[1]->id => 0],
            matchScore: Cache::get("blot_match_score_{$room->id}", [
                'user_' . $players[0]->id => 0,
                'user_' . $players[1]->id => 0
            ]),
            announcedCombinations: [],
            round: 1,
        );
        
        // Save game state to repository
        $this->blotGameStateRepository->saveGameState($room->id, $gameState);
        
        // Broadcast events
        broadcast(new \App\Events\Blot\NewHandStarted($room->id, $proposedTrump))->toOthers();
        
        // Notify dealer to select trump
        broadcast(new PlayerTurn(
            userId: $dealerId,
            availableActions: ['accept_trump', 'reject_trump'],
            callAmount: 0,
            minRaise: 0,
            maxRaise: 0
        ));
    }
    
    /**
     * Handle trump selection
     *
     * @param User $user
     * @param GameRoom $room
     * @param bool $agreed
     * @param string|null $newTrump
     * @return void
     */
    public function handleTrumpSelection(User $user, GameRoom $room, bool $agreed, ?string $newTrump = null): void
    {
        // Load game state from repository
        $gameState = $this->blotGameStateRepository->getGameState($room->id);
        if (!$gameState) {
            throw new InvalidGameActionException('Game state not found');
        }
        
        // Validate it's the correct player's turn
        if ($gameState->currentPlayerId !== $user->id) {
            throw new InvalidGameActionException('Not your turn to select trump');
        }
        
        $players = $room->players()->where('role', 'player')->get();
        $opponent = $players->firstWhere('id', '!=', $user->id);
        
        if ($agreed) {
            // Accept the proposed trump
            $trumpCard = end($gameState->deck); // The last card in deck is the proposed trump
            $newGameState = new BlotGameStateData(
                deck: $gameState->deck,
                trumpSuit: CardSuit::from($trumpCard['suit']),
                dealerId: $gameState->dealerId,
                currentPlayerId: $gameState->dealerId,
                hands: $gameState->hands,
                trick: $gameState->trick,
                scores: $gameState->scores,
                matchScore: $gameState->matchScore,
                announcedCombinations: $gameState->announcedCombinations,
                round: $gameState->round,
            );
            
            // Deal remaining cards (2 to dealer, 3 to opponent)
            $updatedHands = $this->dealRemainingCards($room, $newGameState, true);
            $newGameState = new BlotGameStateData(
                deck: $newGameState->deck,
                trumpSuit: $newGameState->trumpSuit,
                dealerId: $newGameState->dealerId,
                currentPlayerId: $newGameState->dealerId,
                hands: $updatedHands,
                trick: $newGameState->trick,
                scores: $newGameState->scores,
                matchScore: $newGameState->matchScore,
                announcedCombinations: $newGameState->announcedCombinations,
                round: $newGameState->round,
            );
        } else {
            // Reject trump and select new one
            if (!$newTrump) {
                throw new InvalidGameActionException('Must specify new trump suit when rejecting');
            }
            
            $newGameState = new BlotGameStateData(
                deck: $gameState->deck,
                trumpSuit: CardSuit::from($newTrump),
                dealerId: $gameState->dealerId,
                currentPlayerId: $gameState->dealerId,
                hands: $gameState->hands,
                trick: $gameState->trick,
                scores: $gameState->scores,
                matchScore: $gameState->matchScore,
                announcedCombinations: $gameState->announcedCombinations,
                round: $gameState->round,
            );
            
            // Deal remaining cards (3 to dealer, 2 to opponent)
            $updatedHands = $this->dealRemainingCards($room, $newGameState, false);
            $newGameState = new BlotGameStateData(
                deck: $newGameState->deck,
                trumpSuit: $newGameState->trumpSuit,
                dealerId: $newGameState->dealerId,
                currentPlayerId: $newGameState->dealerId,
                hands: $updatedHands,
                trick: $newGameState->trick,
                scores: $newGameState->scores,
                matchScore: $newGameState->matchScore,
                announcedCombinations: $newGameState->announcedCombinations,
                round: $newGameState->round,
            );
        }
        
        // Save updated game state to repository
        $this->blotGameStateRepository->saveGameState($room->id, $newGameState);
        
        // Broadcast updated state
        broadcast(new GameStateUpdated($room->id, $newGameState->toArray()));
        
        // Start first trick - dealer leads
        broadcast(new PlayerTurn(
            userId: $newGameState->dealerId,
            availableActions: ['play_card'],
            callAmount: 0,
            minRaise: 0,
            maxRaise: 0
        ));
    }
    
    /**
     * Handle player move
     *
     * @param User $user
     * @param GameRoom $room
     * @param array $cardData
     * @return void
     */
    public function handlePlayerMove(User $user, GameRoom $room, array $cardData): void
    {
        // Load game state from repository
        $gameState = $this->blotGameStateRepository->getGameState($room->id);
        if (!$gameState) {
            throw new InvalidGameActionException('Game state not found');
        }
        
        // Validate it's the correct player's turn
        if ($gameState->currentPlayerId !== $user->id) {
            throw new InvalidGameActionException('Not your turn to play');
        }
        
        // Validate card is in player's hand
        $playerHand = $gameState->hands['user_' . $user->id];
        $playedCard = null;
        $cardIndex = null;
        
        foreach ($playerHand as $index => $card) {
            if ($card['suit'] === $cardData['suit'] && $card['rank'] === $cardData['rank']) {
                $playedCard = $card;
                $cardIndex = $index;
                break;
            }
        }
        
        if (!$playedCard) {
            throw new InvalidGameActionException('Card not in your hand');
        }
        
        // Validate move according to Blot rules
        $this->validateMove($gameState, $user, $playedCard);
        
        // Remove card from player's hand
        unset($playerHand[$cardIndex]);
        $playerHand = array_values($playerHand); // Re-index array
        
        // Add card to trick
        $trick = $gameState->trick;
        $trick[] = [
            'player_id' => $user->id,
            'card' => $playedCard
        ];
        
        // Update hands
        $hands = $gameState->hands;
        $hands['user_' . $user->id] = $playerHand;
        
        // Check if trick is complete (2 cards)
        if (count($trick) === 2) {
            // Process the completed trick
            $this->processTrick($room, $gameState, $trick, $hands);
        } else {
            // Trick not complete, wait for opponent's move
            $gameState = new BlotGameStateData(
                deck: $gameState->deck,
                trumpSuit: $gameState->trumpSuit,
                dealerId: $gameState->dealerId,
                currentPlayerId: $this->getOpponentId($room, $user->id),
                hands: $hands,
                trick: $trick,
                scores: $gameState->scores,
                matchScore: $gameState->matchScore,
                announcedCombinations: $gameState->announcedCombinations,
                round: $gameState->round,
            );
            
            // Save updated game state to repository
            $this->blotGameStateRepository->saveGameState($room->id, $gameState);
            
            // Broadcast updated state
            broadcast(new GameStateUpdated($room->id, $gameState->toArray()));
            
            // Notify opponent to play
            broadcast(new PlayerTurn(
                userId: $this->getOpponentId($room, $user->id),
                availableActions: ['play_card'],
                callAmount: 0,
                minRaise: 0,
                maxRaise: 0
            ));
        }
    }
    
    /**
     * Process a completed trick
     *
     * @param GameRoom $room
     * @param BlotGameStateData $gameState
     * @param array $trick
     * @param array $hands
     * @return void
     */
    private function processTrick(GameRoom $room, BlotGameStateData $gameState, array $trick, array $hands): void
    {
        // Determine winner of the trick
        $winnerId = $this->determineTrickWinner($trick, $gameState->trumpSuit);
        
        // Calculate points for the trick
        $points = 0;
        foreach ($trick as $play) {
            $card = $play['card'];
            $rank = CardRank::from($card['rank']);
            
            // Points: Ace=11, Ten=10, King=4, Queen=3, Jack=2
            $points += match($rank) {
                CardRank::ACE => 11,
                CardRank::TEN => 10,
                CardRank::KING => 4,
                CardRank::QUEEN => 3,
                CardRank::JACK => 2,
                default => 0
            };
        }
        
        // Update scores
        $scores = $gameState->scores;
        $scores['user_' . $winnerId] += $points;
        
        // Check if hand is finished (no cards left)
        $handFinished = count($hands['user_' . $room->players()->where('role', 'player')->first()->id]) === 0;
        
        if ($handFinished) {
            // Finish the hand
            $this->finishHand($room, $gameState, $scores);
        } else {
            // Continue to next trick
            $gameState = new BlotGameStateData(
                deck: $gameState->deck,
                trumpSuit: $gameState->trumpSuit,
                dealerId: $gameState->dealerId,
                currentPlayerId: $winnerId, // Winner leads next trick
                hands: $hands,
                trick: [], // Clear trick
                scores: $scores,
                matchScore: $gameState->matchScore,
                announcedCombinations: $gameState->announcedCombinations,
                round: $gameState->round + 1,
            );
            
            // Save updated game state to repository
            $this->blotGameStateRepository->saveGameState($room->id, $gameState);
            
            // Broadcast trick result
            broadcast(new \App\Events\Blot\TrickFinished($room->id, $winnerId, $points))->toOthers();
            
            // After a short delay, notify winner to lead next trick
            // In a real implementation, this would be handled with a job or timer
            broadcast(new PlayerTurn(
                userId: $winnerId,
                availableActions: ['play_card'],
                callAmount: 0,
                minRaise: 0,
                maxRaise: 0
            ));
        }
    }
    
    /**
     * Finish the hand and calculate match scores
     *
     * @param GameRoom $room
     * @param BlotGameStateData $gameState
     * @param array $scores
     * @return void
     */
    private function finishHand(GameRoom $room, BlotGameStateData $gameState, array $scores): void
    {
        // Add 10 points for the last trick to the winner
        // In Blot, the last trick is automatically won by the player who wins the previous trick
        $lastTrickWinner = $gameState->currentPlayerId;
        $scores['user_' . $lastTrickWinner] += 10;
        
        // Determine hand winner based on points
        $players = $room->players()->where('role', 'player')->get();
        $player1Id = $players[0]->id;
        $player2Id = $players[1]->id;
        
        $player1Score = $scores['user_' . $player1Id];
        $player2Score = $scores['user_' . $player2Id];
        
        // Apply capot rule (if one player wins all tricks)
        $capot = false;
        if ($player1Score == 0 || $player2Score == 0) {
            $capot = true;
        }
        
        // Determine who chose trump
        $trumpChooserId = $gameState->dealerId;
        
        // Calculate hand result
        $handResult = [
            'player1_id' => $player1Id,
            'player1_score' => $player1Score,
            'player2_id' => $player2Id,
            'player2_score' => $player2Score,
            'trump_chooser' => $trumpChooserId,
            'capot' => $capot,
        ];
        
        // Update match score
        $matchScore = $gameState->matchScore;
        
        if ($capot) {
            // Capot - winner gets 3 points
            if ($player1Score > $player2Score) {
                $matchScore['user_' . $player1Id] += 3;
            } else {
                $matchScore['user_' . $player2Id] += 3;
            }
        } else {
            // Normal scoring
            if ($player1Score > $player2Score) {
                // Player 1 wins
                $trumpChooserWon = ($trumpChooserId === $player1Id);
                if ($trumpChooserWon) {
                    // Trump chooser won, gets 1 point
                    $matchScore['user_' . $player1Id] += 1;
                } else {
                    // Non-trump chooser won, gets 2 points
                    $matchScore['user_' . $player1Id] += 2;
                }
            } elseif ($player2Score > $player1Score) {
                // Player 2 wins
                $trumpChooserWon = ($trumpChooserId === $player2Id);
                if ($trumpChooserWon) {
                    // Trump chooser won, gets 1 point
                    $matchScore['user_' . $player2Id] += 1;
                } else {
                    // Non-trump chooser won, gets 2 points
                    $matchScore['user_' . $player2Id] += 2;
                }
            }
            // If tied, no points awarded
        }
        
        // Save updated match score to repository
        $this->blotGameStateRepository->saveMatchScore($room->id, $matchScore);
        
        // Check if match is finished (first to 11 points)
        $matchFinished = false;
        $matchWinner = null;
        foreach ($matchScore as $userId => $score) {
            if ($score >= 11) {
                $matchFinished = true;
                $matchWinner = $userId;
                break;
            }
        }
        
        // Broadcast hand result
        broadcast(new \App\Events\Blot\HandFinished($room->id, $handResult, $matchScore, $matchFinished))->toOthers();
        
        if ($matchFinished) {
            // Match finished, announce winner
            broadcast(new \App\Events\Blot\MatchFinished($room->id, $matchWinner))->toOthers();
        } else {
            // Start new hand after delay
            StartNewBlotHand::dispatch($room)->delay(now()->addSeconds(5));
        }
    }
    
    /**
     * Create a 24-card Blot deck (9 to Ace)
     *
     * @return array
     */
    private function createBlotDeck(): array
    {
        $deck = [];
        
        foreach (CardSuit::cases() as $suit) {
            // Only use ranks 9 through Ace for Blot
            foreach ([9, 10, 11, 12, 13, 14] as $rankValue) {
                $rank = CardRank::from($rankValue);
                $deck[] = [
                    'suit' => $suit->value,
                    'rank' => $rank->value,
                    'display' => $rank->displayValue() . $suit->symbol(),
                    'suit_symbol' => $suit->symbol(),
                    'rank_display' => $rank->displayValue(),
                ];
            }
        }
        
        return $deck;
    }
    
    /**
     * Deal remaining cards after trump selection
     *
     * @param GameRoom $room
     * @param BlotGameStateData $gameState
     * @param bool $dealerAccepted True if dealer accepted trump (gets 2 cards), false if rejected (gets 3)
     * @return array Updated hands
     */
    private function dealRemainingCards(GameRoom $room, BlotGameStateData $gameState, bool $dealerAccepted): array
    {
        $players = $room->players()->where('role', 'player')->get();
        $dealer = $players->firstWhere('id', $gameState->dealerId);
        $opponent = $players->firstWhere('id', '!=', $gameState->dealerId);
        
        $deck = $gameState->deck;
        
        // Remove the trump card from the deck (it's already been turned up)
        array_pop($deck);
        
        if ($dealerAccepted) {
            // Dealer gets 2 cards, opponent gets 3
            $dealerCards = array_splice($deck, 0, 2);
            $opponentCards = array_splice($deck, 0, 3);
        } else {
            // Dealer gets 3 cards, opponent gets 2
            $dealerCards = array_splice($deck, 0, 3);
            $opponentCards = array_splice($deck, 0, 2);
        }
        
        // Update hands
        $hands = $gameState->hands;
        $hands['user_' . $dealer->id] = array_merge($hands['user_' . $dealer->id], $dealerCards);
        $hands['user_' . $opponent->id] = array_merge($hands['user_' . $opponent->id], $opponentCards);
        
        return $hands;
    }
    
    /**
     * Validate a player's move according to Blot rules
     *
     * @param BlotGameStateData $gameState
     * @param User $user
     * @param array $card
     * @return void
     * @throws InvalidGameActionException
     */
    private function validateMove(BlotGameStateData $gameState, User $user, array $card): void
    {
        // If this is the first card of the trick, no restrictions
        if (empty($gameState->trick)) {
            return;
        }
        
        // This is the second card, must follow suit if possible
        $leadCard = $gameState->trick[0]['card'];
        $leadSuit = CardSuit::from($leadCard['suit']);
        $playedSuit = CardSuit::from($card['suit']);
        $trumpSuit = $gameState->trumpSuit;
        
        // Get player's hand
        $playerHand = $gameState->hands['user_' . $user->id];
        
        // Check if player has cards of the lead suit
        $hasLeadSuit = false;
        foreach ($playerHand as $handCard) {
            if (CardSuit::from($handCard['suit']) === $leadSuit) {
                $hasLeadSuit = true;
                break;
            }
        }
        
        // If player has lead suit, they must follow it
        if ($hasLeadSuit && $playedSuit !== $leadSuit) {
            throw new InvalidGameActionException('Must follow suit if you can');
        }
        
        // If trump was led and player doesn't have lead suit, check if they must trump
        if ($leadSuit === $trumpSuit && !$hasLeadSuit) {
            // Player doesn't have trump suit, so any card is valid
            return;
        }
        
        // If trump was led and player has trump, they must play trump
        if ($leadSuit === $trumpSuit && $hasLeadSuit) {
            if ($playedSuit !== $trumpSuit) {
                throw new InvalidGameActionException('Must play trump if you have it when trump is led');
            }
        }
        
        // If non-trump was led and player doesn't have lead suit but has trump,
        // they may choose to trump or play another suit
        // This is a valid move in Blot
    }
    
    /**
     * Determine the winner of a trick
     *
     * @param array $trick
     * @param CardSuit|null $trumpSuit
     * @return int
     */
    private function determineTrickWinner(array $trick, ?CardSuit $trumpSuit): int
    {
        $firstCard = $trick[0]['card'];
        $secondCard = $trick[1]['card'];
        
        $firstSuit = CardSuit::from($firstCard['suit']);
        $secondSuit = CardSuit::from($secondCard['suit']);
        $firstRank = CardRank::from($firstCard['rank']);
        $secondRank = CardRank::from($secondCard['rank']);
        
        // If second card is trump and first is not, second wins
        if ($trumpSuit && $secondSuit === $trumpSuit && $firstSuit !== $trumpSuit) {
            return $trick[1]['player_id'];
        }
        
        // If first card is trump and second is not, first wins
        if ($trumpSuit && $firstSuit === $trumpSuit && $secondSuit !== $trumpSuit) {
            return $trick[0]['player_id'];
        }
        
        // If both cards are trump or both are non-trump of the same suit, compare ranks
        if ($firstSuit === $secondSuit) {
            // In Blot, card order is: Ace, Ten, King, Queen, Jack, Nine
            $blotRankOrder = [
                CardRank::ACE->value => 6,
                CardRank::TEN->value => 5,
                CardRank::KING->value => 4,
                CardRank::QUEEN->value => 3,
                CardRank::JACK->value => 2,
                CardRank::NINE->value => 1,
            ];
            
            $firstRankOrder = $blotRankOrder[$firstRank->value] ?? 0;
            $secondRankOrder = $blotRankOrder[$secondRank->value] ?? 0;
            
            return $firstRankOrder > $secondRankOrder ? $trick[0]['player_id'] : $trick[1]['player_id'];
        }
        
        // If different suits and neither is trump, first card wins (lead wins)
        return $trick[0]['player_id'];
    }
    
    /**
     * Get opponent's user ID
     *
     * @param GameRoom $room
     * @param int $userId
     * @return int
     */
    private function getOpponentId(GameRoom $room, int $userId): int
    {
        $players = $room->players()->where('role', 'player')->get();
        return $players->firstWhere('id', '!=', $userId)?->id ?: $players->first()->id;
    }
}