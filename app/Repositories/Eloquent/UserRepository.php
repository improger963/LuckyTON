<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserRepository extends \App\Repositories\BaseRepository
{
    /**
     * UserRepository constructor.
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Find user by referral code
     *
     * @param string $referralCode
     * @return User|null
     */
    public function findByReferralCode(string $referralCode): ?User
    {
        return $this->model->where('referral_code', $referralCode)->first();
    }

    /**
     * Get users with referrals
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersWithReferrals()
    {
        return $this->model->with('referrals')->get();
    }

    /**
     * Get premium users
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPremiumUsers()
    {
        return $this->model->premium()->get();
    }

    /**
     * Get user registration data for chart
     *
     * @param int $days
     * @return \Illuminate\Support\Collection
     */
    public function getRegistrationChartData(int $days = 7)
    {
        return $this->model->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays($days - 1))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    }
}