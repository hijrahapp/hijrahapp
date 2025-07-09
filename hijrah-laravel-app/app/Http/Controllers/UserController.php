<?php

namespace App\Http\Controllers;

use App\Http\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use OpenApi\Annotations as OA;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function create(Request $request) {
        return response()->json($this->userService->createUser($request->all()));
    }

    /**
     * @OA\Get(
     *     path="/user/all",
     *     summary="List users",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function all() {
        return response()->json($this->userService->getAllUsers());
    }

    public function resetPassword(Request $request) {
        return response()->json($this->userService->resetPassword($request->authUser, $request->password));
    }
}
