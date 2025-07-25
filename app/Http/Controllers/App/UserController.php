<?php

namespace App\Http\Controllers\App;

use App\Http\Repositories\UserRepository;
use App\Mail\WelcomeAdminMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController
{
    public function __construct(private UserRepository $userRepo) {}

    public function createNewUser(array $data) {
        $user = $this->userRepo->findByEmail($data['email']);
        if ($user) {
            return ['error' => __('messages.email_exists')];
        }

        $tempPassword = $data['password'];

        $data['active'] = true;
        $data['password'] = Hash::make($data['password']);
        $user = $this->userRepo->create($data);

        if(config('app.features.email_verification')) {
            Mail::to($user->email)->send(new WelcomeAdminMail($user, $tempPassword));
        }

        return ['message' => __('messages.user_created_successfully')];
    }
}
