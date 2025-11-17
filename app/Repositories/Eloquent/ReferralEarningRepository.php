<?php

namespace App\Repositories\Eloquent;

use App\Models\ReferralEarning;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReferralEarningRepository extends \App\Repositories\BaseRepository
{
    /**
     * ReferralEarningRepository constructor.
     *
     * @param ReferralEarning $model
     */
    public function __construct(ReferralEarning $model)
    {
        parent::__construct($model);
    }

    /**
     * Get referral activity for user
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getReferralActivityForUser(int $userId, int $limit = 10)
    {
        return DB::table('referral_earnings')
            ->join('users', 'referral_earnings.referral_id', '=', 'users.id')
            ->where('referral_earnings.user_id', $userId)
            ->select([
                'users.id',
                'users.username',
                'users.avatar',
                'users.created_at as joined_at',
                DB::raw('SUM(referral_earnings.amount) as total_earnings'),
                DB::raw('COUNT(referral_earnings.id) as earnings_count'),
            ])
            ->groupBy('users.id', 'users.username', 'users.avatar', 'users.created_at')
            ->orderByDesc('total_earnings')
            ->limit($limit)
            ->get();
    }

    /**
     * Get detailed referral statistics for user
     *
     * @param int $userId
     * @return object
     */
    public function getDetailedStatsForUser(int $userId)
    {
        return DB::table('referral_earnings')
            ->where('user_id', $userId)
            ->select([
                DB::raw('SUM(amount) as total_earnings'),
                DB::raw('COUNT(DISTINCT referral_id) as active_referrals'),
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('AVG(amount) as average_earning'),
                DB::raw('MAX(amount) as max_earning'),
            ])
            ->first();
    }

    /**
     * Get active referrals count for user
     *
     * @param int $userId
     * @return int
     */
    public function getActiveReferralsCountForUser(int $userId)
    {
        return DB::table('referral_earnings')
            ->where('user_id', $userId)
            ->distinct('referral_id')
            ->count('referral_id');
    }
}