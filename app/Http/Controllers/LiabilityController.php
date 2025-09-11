<?php

namespace App\Http\Controllers;

use App\Http\Repositories\LiabilityRepository;
use App\Resources\LiabilityDetailedResource;
use App\Resources\LiabilityResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LiabilityController
{
    public function __construct(private LiabilityRepository $liabilityRepo) {}

    /**
     * Get liabilities that the user is eligible for based on completed modules.
     */
    public function getMyLiabilities(Request $request): JsonResponse
    {
        try {
            $user = $request->authUser;

            // Get filter parameters
            $methodologyIds = $request->input('methodologyIds', []);
            $moduleIds = $request->input('moduleIds', []);
            $status = $request->input('status', []);

            // Ensure array inputs
            if (is_string($methodologyIds)) {
                $methodologyIds = explode(',', $methodologyIds);
            }
            if (is_string($moduleIds)) {
                $moduleIds = explode(',', $moduleIds);
            }
            if (is_string($status)) {
                $status = explode(',', $status);
            }

            $liabilities = $this->liabilityRepo->getUserLiabilities(
                $user->id,
                array_filter($methodologyIds),
                array_filter($moduleIds),
                array_filter($status)
            );

            return response()->json(LiabilityResource::collection($liabilities));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_liabilities'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get detailed liability information.
     */
    public function get(Request $request, int $liabilityId): JsonResponse
    {
        try {
            $user = $request->authUser;
            $liability = $this->liabilityRepo->getLiabilityWithUserProgress($liabilityId, $user->id);

            if (! $liability) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.liability_not_found'),
                ], 404);
            }

            return response()->json(new LiabilityDetailedResource($liability));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_liability'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update todo completion status.
     */
    public function updateTodo(Request $request, int $liabilityId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'todo_id' => 'required|integer|min:0',
            'is_completed' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_error'),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->authUser;
            $success = $this->liabilityRepo->updateTodoCompletion(
                $user->id,
                $liabilityId,
                $request->todo_id,
                $request->is_completed
            );

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.error_updating_todo'),
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.todo_updated_successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_updating_todo'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete liability if all todos are done.
     */
    public function completeLiability(Request $request, int $liabilityId): JsonResponse
    {
        try {
            $user = $request->authUser;
            $result = $this->liabilityRepo->completeLiability($user->id, $liabilityId);

            if (! $result['success']) {
                switch ($result['error']) {
                    case 'liability_not_found':
                        return response()->json([
                            'success' => false,
                            'message' => __('messages.liability_not_found'),
                        ], 404);

                    case 'todos_not_completed':
                        return response()->json([
                            'success' => false,
                            'message' => __('messages.todos_not_completed'),
                        ], 400);

                    default:
                        return response()->json([
                            'success' => false,
                            'message' => __('messages.error_completing_liability'),
                        ], 500);
                }
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.liability_completed_successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_completing_liability'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getMyLiabilitiesFilters(Request $request): JsonResponse
    {
        try {
            $user = $request->authUser;
            $filters = $this->liabilityRepo->getUserLiabilitiesFilters($user->id);

            return response()->json($filters);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_filters'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
