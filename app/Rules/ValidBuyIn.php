<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\GameRoom;

class ValidBuyIn implements ValidationRule
{
    protected $gameRoom;

    public function __construct(GameRoom $gameRoom)
    {
        $this->gameRoom = $gameRoom;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Use the stake field from the game room for validation
        $stake = $this->gameRoom->stake;
        
        if ($value < $stake) {
            $fail('Buy-in amount is below the minimum stake for this room.');
        }

        if ($value > $stake) {
            $fail('Buy-in amount exceeds the maximum stake for this room.');
        }
    }
}