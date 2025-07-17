<?php

namespace App\Http\Services;

use App\Enums\RoleName;
use App\Http\Repositories\RoleRepository;
use App\Http\Repositories\UserRepository;
use App\Mail\PasswordResetSuccessMail;
use App\Models\Role;
use App\Resources\UserResource;
use App\Utils\JWTUtils;
use Illuminate\Support\Facades\Hash;
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
        if (Hash::check($password, $user->password)) {
            return response()->json(['message' => __('messages.cannot_enter_same_password')], 401);
        }

        logger('password');
        logger($password);

        $user->password = $password;
        $user->save();

        // Send password reset success email
        if(config('app.features.email_verification')) {
            Mail::to($user->email)->send(new PasswordResetSuccessMail($user));
        }

        return response()->json(JWTUtils::generateTokenResponse($user));
    }

    public function resetPasswordWithCurrent($user, $currentPassword, $newPassword) {
        if (!Hash::check($currentPassword, $user->password)) {
            return ['error' => __('messages.current_password_incorrect')];
        }
        if (Hash::check($newPassword, $user->password)) {
            return ['error' => __('messages.cannot_enter_same_password')];
        }
        $user->password = $newPassword;
        $user->save();
        return ['message' => __('messages.password_reset_success')];
    }

    public function deleteUser($userEmail): bool {
        $user = $this->userRepo->findByEmail($userEmail);

        if (!$user) {
            return false;
        }

        return $this->userRepo->delete($user);
    }

    public function updateUser($user, array $data) {
        $this->userRepo->update($user->id, $data);
        $user->refresh();

        logger('user-updated');
        logger($user);
        return (new UserResource($user))->userArray();
    }
}
