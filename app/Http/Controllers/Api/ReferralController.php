<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Traits\ApiResponser;
use App\Repositories\Eloquent\ReferralEarningRepository;
use App\Repositories\Eloquent\UserRepository;
use Exception;

class ReferralController extends Controller
{
    use ApiResponser;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var ReferralEarningRepository
     */
    protected $referralEarningRepository;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * ReferralController constructor.
     *
     * @param UserService $userService
     * @param ReferralEarningRepository $referralEarningRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        UserService $userService,
        ReferralEarningRepository $referralEarningRepository,
        UserRepository $userRepository
    ) {
        $this->userService = $userService;
        $this->referralEarningRepository = $referralEarningRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Get referral statistics and information
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $referralStats = $this->getReferralStats($user);

        // Transform referral stats data
        $transformedReferralStats = [
            'total_earnings' => [
                'amount' => (float) $referralStats['total_earnings'],
                'formatted' => number_format($referralStats['total_earnings'], 2) . ' USD',
            ],
            'referral_count' => $referralStats['referral_count'],
            'referral_links' => $referralStats['referral_links'],
            'activity' => $referralStats['activity'],
            'stats' => [
                'total_earnings' => (float) $referralStats['stats']['total_earnings'],
                'active_referrals' => $referralStats['stats']['active_referrals'],
                'total_transactions' => $referralStats['stats']['total_transactions'],
                'average_earning' => (float) $referralStats['stats']['average_earning'],
                'max_earning' => (float) $referralStats['stats']['max_earning'],
                'conversion_rate' => (float) $referralStats['stats']['referral_conversion_rate'],
                'formatted_conversion_rate' => $referralStats['stats']['referral_conversion_rate'] . '%',
            ],
            'share_content' => [
                'message' => 'Join our amazing gaming platform! Use my referral code: ' . $user->referral_code,
                'title' => 'Join and get bonus!',
            ],
        ];

        return $this->successResponse($transformedReferralStats, 'Referral statistics fetched successfully');
    }

    /**
     * Get referral activity
     */
    public function activity(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $limit = $request->get('limit', 10);

            $activity = $this->referralEarningRepository->getReferralActivityForUser($user->id, $limit);

            $formattedActivity = $activity->map(function ($item) {
                return [
                    'id' => $item->id,
                    'username' => $item->username,
                    'avatar' => $item->avatar,
                    'joined_at' => $item->joined_at,
                    'total_earnings' => (float) $item->total_earnings,
                    'earnings_count' => $item->earnings_count,
                    'formatted_earnings' => '+' . number_format($item->total_earnings, 2) . ' USD',
                    // Можно добавить метрики игр когда будет готова игровая логика
                    'games_played' => rand(1, 50), // Заглушка
                    'last_activity' => now()->subDays(rand(0, 30))->format('Y-m-d H:i:s'),
                ];
            })->toArray();

            return $this->successResponse($formattedActivity, 'Referral activity fetched successfully');
        } catch (Exception $e) {
            Log::error('Error fetching referral activity', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('An error occurred while fetching referral activity.', 500);
        }
    }

    /**
     * Get referral links
     */
    public function links(Request $request): JsonResponse
    {
        $user = $request->user();
        $referralStats = $this->getReferralStats($user);
        $links = $referralStats['referral_links'];

        return $this->successResponse($links, 'Referral links fetched successfully');
    }

    /**
     * Share referral content
     */
    public function share(Request $request): JsonResponse
    {
        $user = $request->user();
        $platform = $request->get('platform', 'telegram');

        $shareContent = [
            'telegram' => [
                'text' => "🎮 Join our amazing gaming platform!\n\nUse my referral code: {$user->referral_code}\n\nGet bonus when you sign up! 🎁",
                'url' => 't.me/pokergame_bot?ref=' . $user->referral_code,
            ],
            'web' => [
                'text' => "Join our gaming platform and get bonus! Use my referral code: {$user->referral_code}",
                'url' => url('/register?ref=' . $user->referral_code),
            ],
            'direct' => [
                'text' => "Check out this gaming platform! Use code: {$user->referral_code}",
                'url' => config('app.url') . '/ref/' . $user->referral_code,
            ],
        ];

        return $this->successResponse($shareContent[$platform] ?? $shareContent['telegram'], 'Share content fetched successfully');
    }

    /**
     * Get referral statistics for user
     */
    private function getReferralStats($user): array
    {
        return [
            'total_earnings' => $this->getTotalEarnings($user),
            'referral_count' => $this->getReferralCount($user),
            'referral_links' => $this->generateReferralLinks($user),
            'activity' => $this->getReferralActivity($user),
            'stats' => $this->getDetailedStats($user),
        ];
    }

    /**
     * Get total referral earnings
     */
    private function getTotalEarnings($user): float
    {
        return (float) $user->referralEarnings()->sum('amount');
    }

    /**
     * Get total number of referrals
     */
    private function getReferralCount($user): int
    {
        return $user->referrals()->count();
    }

    /**
     * Generate multiple referral links
     */
    private function generateReferralLinks($user): array
    {
        return [
            'telegram' => 't.me/pokergame_bot?ref=' . $user->referral_code,
            'web' => url('/register?ref=' . $user->referral_code),
            'direct' => config('app.url') . '/ref/' . $user->referral_code,
        ];
    }

    /**
     * Get referral activity with detailed information
     */
    private function getReferralActivity($user, int $limit = 10): array
    {
        $activity = \Illuminate\Support\Facades\DB::table('referral_earnings')
            ->join('users', 'referral_earnings.referral_id', '=', 'users.id')
            ->where('referral_earnings.user_id', $user->id)
            ->select([
                'users.id',
                'users.username',
                'users.avatar',
                'users.created_at as joined_at',
                \Illuminate\Support\Facades\DB::raw('SUM(referral_earnings.amount) as total_earnings'),
                \Illuminate\Support\Facades\DB::raw('COUNT(referral_earnings.id) as earnings_count'),
            ])
            ->groupBy('users.id', 'users.username', 'users.avatar', 'users.created_at')
            ->orderByDesc('total_earnings')
            ->limit($limit)
            ->get();

        return $activity->map(function ($item) {
            return [
                'id' => $item->id,
                'username' => $item->username,
                'avatar' => $item->avatar,
                'joined_at' => $item->joined_at,
                'total_earnings' => (float) $item->total_earnings,
                'earnings_count' => $item->earnings_count,
                'formatted_earnings' => '+' . number_format($item->total_earnings, 2) . ' USD',
                // Можно добавить метрики игр когда будет готова игровая логика
                'games_played' => rand(1, 50), // Заглушка
                'last_activity' => now()->subDays(rand(0, 30))->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    /**
     * Get detailed referral statistics
     */
    private function getDetailedStats($user): array
    {
        $stats = $this->referralEarningRepository->getDetailedStatsForUser($user->id);

        return [
            'total_earnings' => (float) ($stats->total_earnings ?? 0),
            'active_referrals' => (int) ($stats->active_referrals ?? 0),
            'total_transactions' => (int) ($stats->total_transactions ?? 0),
            'average_earning' => (float) ($stats->average_earning ?? 0),
            'max_earning' => (float) ($stats->max_earning ?? 0),
            'referral_conversion_rate' => $this->calculateConversionRate($user),
        ];
    }

    /**
     * Calculate referral conversion rate
     */
    private function calculateConversionRate($user): float
    {
        $totalReferrals = $user->referrals()->count();
        $activeReferrals = $this->referralEarningRepository->getActiveReferralsCountForUser($user->id);

        if ($totalReferrals === 0) {
            return 0.0;
        }

        return round(($activeReferrals / $totalReferrals) * 100, 2);
    }
}