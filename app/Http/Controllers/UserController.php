<?php

namespace App\Http\Controllers;

use App\Http\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function create(Request $request) {
        return response()->json($this->userService->createUser($request->all()));
    }

    public function all() {
        return response()->json($this->userService->getAllUsers());
    }

    public function delete(Request $request) {
        $deleted = $this->userService->deleteUser($request->email);

        if($deleted) {
            return response()->json(['message' => __('messages.user_deleted')], 200);
        }

        return response()->json(['message' => __('messages.user_not_found')], 404);
    }
}
