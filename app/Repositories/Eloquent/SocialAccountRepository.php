<?php

namespace App\Repositories\Eloquent;

use App\Models\SocialAccount;
use App\Repositories\BaseRepository;

class SocialAccountRepository extends BaseRepository
{
    /**
     * SocialAccountRepository constructor.
     *
     * @param SocialAccount $model
     */
    public function __construct(SocialAccount $model)
    {
        parent::__construct($model);
    }
}