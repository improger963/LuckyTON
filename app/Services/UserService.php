<?php

namespace App\Services;

use App\Repositories\Eloquent\UserRepository;

class UserService
{
    /**
     * UserService constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(protected UserRepository $userRepository)
    {
        //
    }

    /**
     * Get user by ID
     *
     * @param int $id
     * @return \App\Models\User|null
     */
    public function getUserById(int $id)
    {
        return $this->userRepository->find($id);
    }

    /**
     * Get user by referral code
     *
     * @param string $referralCode
     * @return \App\Models\User|null
     */
    public function getUserByReferralCode(string $referralCode)
    {
        return $this->userRepository->findByReferralCode($referralCode);
    }

    /**
     * Get all users with referrals
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersWithReferrals()
    {
        return $this->userRepository->getUsersWithReferrals();
    }

    /**
     * Get premium users
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPremiumUsers()
    {
        return $this->userRepository->getPremiumUsers();
    }
}