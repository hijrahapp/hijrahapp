<?php

namespace App\Http\Controllers;

use App\Http\Resources\InterestResource;
use App\Models\Interest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class InterestController extends Controller
{
    /**
     * Get all interests.
     */
    public function all(): JsonResponse
    {
        $interests = Interest::all();

        return response()->json(InterestResource::collection($interests));
    }

    /**
     * Update user interests.
     */
    public function updateUserInterests(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'interests' => ['required', 'array'],
                'interests.*' => ['integer', 'exists:interests,id'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.validation_failed'),
                    'errors' => $validator->errors(),
                ], 400);
            }

            $userId = $request->authUserId ?? $request->authUser?->id;

            $user = User::find($userId);

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.user_not_found'),
                ], 404);
            }

            $user->update([
                'interests' => $request->interests,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('messages.user_interests_updated_successfully'),
                'interests' => $user->interests,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.an_error_occurred').$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user interests.
     */
    public function getUserInterests(Request $request): JsonResponse
    {
        $userId = $request->authUserId ?? $request->authUser?->id;

        $user = User::find($userId);

        if (! $user) {
            return response()->json(['message' => __('messages.user_not_found')], 404);
        }

        $userInterestIds = $user->interests ?? [];
        $interests = Interest::whereIn('id', $userInterestIds)->get();

        return response()->json(InterestResource::collection($interests));
    }
}
