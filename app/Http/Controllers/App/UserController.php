<?php

namespace App\Http\Controllers\App;

use App\Http\Middleware\UserMiddleware;
use App\Http\Services\UserService;
use App\Resources\UserResource;
use App\Utils\JWTUtils;

class UserDetails
{
    public function __construct(private UserRepository $userRepo) {}

    public function createNewUser(array $data) {
        $user = $this->userRepo->findByEmail($request['email']);
        if ($user) {
            return ['error' => __('messages.email_exists')];
        }

        $data['active'] = true;
        $data['password'] = Hash::make($data['password']);
        $this->userRepo->create($data);

        return ['message' => __('messages.user_created_successfully')];
    }
}
