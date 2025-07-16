<?php

namespace App\Http\Services;

use App\Enums\RoleName;
use App\Http\Repositories\RoleRepository;
use App\Http\Repositories\UserRepository;
use App\Mail\PasswordResetSuccessMail;
use App\Models\Role;
use App\Resources\UserResource;
use App\Utils\JWTUtils;
use Illuminate\Support\Facades\Mail;

class UserService
{
    public function __construct(private UserRepository $userRepo, private RoleRepository $roleRepo) {}

    public function createUser(array $data) {
        if (isset($data['role'])) {
            $role = $this->roleRepo->findByName($data['role']);
            $data['roleId'] = $role->id;
            unset($data['role']);
        }
        $data['active'] = true;

        return new UserResource($this->userRepo->create($data));
    }

    public function getAllUsers() {
        return $this->userRepo->getAll();
    }

    public function resetPassword($user, $password) {
        $user->password = $password;
        $user->save();

        // Send password reset success email
        if(config('app.features.email_verification')) {
            Mail::to($user->email)->send(new PasswordResetSuccessMail($user));
        }

        return JWTUtils::generateTokenResponse($user);
    }

    public function deleteUser($userEmail): bool {
        $user = $this->userRepo->findByEmail($userEmail);

        if (!$user) {
            return false;
        }

        return $this->userRepo->delete($user);
    }
}
