<?php

namespace App\Http\Controllers\App;

use App\Http\Middleware\UserMiddleware;
use App\Http\Services\UserService;
use App\Resources\UserResource;
use App\Utils\JWTUtils;

class UserDetailsController
{
    public function __construct(private UserMiddleware $userMiddleware, private UserService $userService) {}
    public function getAllUserDetails($jwt) {
        if (!$jwt) {
            return ['message' => 'Session expired. Please restart the reset process.'];
        }

        $decodedToken = JWTUtils::decodeToken($jwt);

        if(isset($decodedToken['message'])) {
            return $decodedToken;
        }

        $user = $this->userMiddleware->fetchAndValidateUser($decodedToken['sub']);

        if(isset($user['message'])) {
            return $user;
        }

        return $user;
    }

    public function getUserDetails($jwt)
    {
        return (new UserResource($this->getAllUserDetails($jwt)))->userArray();
    }

    public function updateUserDetails($jwt, $data)
    {
        $user = $this->getAllUserDetails($jwt);

        if (isset($user['message'])) {
            return $user;
        }

        return $this->userService->updateUser($user, $data);
    }
}
