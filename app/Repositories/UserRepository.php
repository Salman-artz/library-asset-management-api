<?php

namespace App\Repositories;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\EloquentUserRepository;

class UserRepository extends EloquentUserRepository implements UserRepositoryInterface {}
