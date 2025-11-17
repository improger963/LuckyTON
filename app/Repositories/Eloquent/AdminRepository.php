<?php

namespace App\Repositories\Eloquent;

use App\Models\Admin;
use App\Repositories\BaseRepository;

class AdminRepository extends BaseRepository
{
    /**
     * AdminRepository constructor.
     *
     * @param Admin $model
     */
    public function __construct(Admin $model)
    {
        parent::__construct($model);
    }
}