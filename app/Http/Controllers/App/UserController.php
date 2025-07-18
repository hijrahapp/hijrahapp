<?php

namespace App\Http\Controllers\App;

use App\Http\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class UserController
{
    public function __construct(private UserRepository $userRepo) {}

    public function createNewUser(array $data) {
        $user = $this->userRepo->findByEmail($data['email']);
        if ($user) {
            return ['error' => __('messages.email_exists')];
        }

        $data['active'] = true;
        $data['password'] = Hash::make($data['password']);
        $this->userRepo->create($data);

        return ['message' => __('messages.user_created_successfully')];
    }
}
