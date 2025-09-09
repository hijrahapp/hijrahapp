<?php

namespace App\Http\Controllers;

use App\Http\Repositories\ProgramRepository;
use App\Resources\ProgramDetailedResource;
use App\Resources\ProgramResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramController
{
    public function __construct(private ProgramRepository $programRepo) {}

    public function getSuggestedPrograms(Request $request): JsonResponse
    {
        try {
            $user = $request->authUser;
            $programs = $this->programRepo->getSuggestedPrograms($user->id);

            return response()->json(ProgramResource::collection($programs));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_programs'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getMyPrograms(Request $request): JsonResponse
    {
        try {
            $user = $request->authUser;
            $programs = $this->programRepo->getUserPrograms($user->id);

            return response()->json(ProgramResource::collection($programs));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_user_programs'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function get(int $programId): JsonResponse
    {
        try {
            $program = $this->programRepo->findById($programId);

            if (! $program) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.program_not_found'),
                ], 404);
            }

            return response()->json(new ProgramDetailedResource($program));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_program'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function startProgram(Request $request, int $programId): JsonResponse
    {
        try {
            $user = $request->authUser;
            $success = $this->programRepo->startProgram($user->id, $programId);

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.program_not_found_or_already_started'),
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.program_started_successfully'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_starting_program'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function completeProgram(Request $request, int $programId): JsonResponse
    {
        try {
            $user = $request->authUser;
            $success = $this->programRepo->completeProgram($user->id, $programId);

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.program_not_found_or_not_in_progress'),
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.program_completed_successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_completing_program'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
