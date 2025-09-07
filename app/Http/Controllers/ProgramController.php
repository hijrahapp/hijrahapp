<?php

namespace App\Http\Controllers;

use App\Http\Repositories\ProgramRepository;
use App\Resources\ProgramDetailedResource;
use App\Resources\ProgramResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProgramController
{
    public function __construct(private ProgramRepository $programRepo) {}

    public function all(Request $request): JsonResponse
    {
        try {
            // Get programs eligible for the authenticated user based on their module scores
            $user = $request->authUser ?? null;

            if ($user) {
                $programs = $this->programRepo->getProgramsForUser($user->id);
            } else {
                // Fallback to all programs if no authenticated user
                $programs = $this->programRepo->getAll();
            }

            return response()->json(ProgramResource::collection($programs));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_programs'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function get(int $programId): JsonResponse
    {
        try {
            $program = $this->programRepo->findByIdWithModules($programId);

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

    public function create(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'definition' => 'required|string',
                'objectives' => 'required|string',
            ]);

            $program = $this->programRepo->create($validated);

            return response()->json(new ProgramResource($program), 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_creating_program'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function attachModule(Request $request, int $programId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'module_id' => 'required|integer|exists:modules,id',
                'methodology_id' => 'required|integer|exists:methodology,id',
                'pillar_id' => 'nullable|integer|exists:pillars,id',
                'min_score' => 'nullable|numeric|min:0|max:999.99',
                'max_score' => 'nullable|numeric|min:0|max:999.99',
            ]);

            // Validate that max_score is greater than min_score
            if (isset($validated['min_score'], $validated['max_score']) && $validated['min_score'] >= $validated['max_score']) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.max_score_must_be_greater_than_min_score'),
                ], 422);
            }

            $success = $this->programRepo->attachModule($programId, $validated);

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.program_module_relationship_exists_or_program_not_found'),
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.module_attached_successfully'),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_attaching_module'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function detachModule(Request $request, int $programId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'module_id' => 'required|integer|exists:modules,id',
                'methodology_id' => 'required|integer|exists:methodology,id',
                'pillar_id' => 'nullable|integer|exists:pillars,id',
            ]);

            $success = $this->programRepo->detachModule(
                $programId,
                $validated['module_id'],
                $validated['methodology_id'],
                $validated['pillar_id'] ?? null
            );

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.program_module_relationship_not_found'),
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.module_detached_successfully'),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_detaching_module'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateModuleScores(Request $request, int $programId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'module_id' => 'required|integer|exists:modules,id',
                'methodology_id' => 'required|integer|exists:methodology,id',
                'pillar_id' => 'nullable|integer|exists:pillars,id',
                'min_score' => 'required|numeric|min:0|max:999.99',
                'max_score' => 'required|numeric|min:0|max:999.99',
            ]);

            // Validate that max_score is greater than min_score
            if ($validated['min_score'] >= $validated['max_score']) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.max_score_must_be_greater_than_min_score'),
                ], 422);
            }

            $success = $this->programRepo->updateModuleScores(
                $programId,
                $validated['module_id'],
                $validated['methodology_id'],
                $validated['pillar_id'] ?? null,
                [
                    'min_score' => $validated['min_score'],
                    'max_score' => $validated['max_score'],
                ]
            );

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.program_not_found'),
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.module_scores_updated_successfully'),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_updating_module_scores'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAvailableModules(int $methodologyId): JsonResponse
    {
        try {
            $modules = $this->programRepo->getAvailableModulesForMethodology($methodologyId);

            return response()->json([
                'success' => true,
                'data' => $modules,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_available_modules'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getWithModulesForMethodology(int $programId, int $methodologyId): JsonResponse
    {
        try {
            $program = $this->programRepo->findByIdWithModulesForMethodology($programId, $methodologyId);

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

    public function update(Request $request, int $programId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'definition' => 'sometimes|string',
                'objectives' => 'sometimes|string',
            ]);

            $success = $this->programRepo->update($programId, $validated);

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.program_not_found'),
                ], 404);
            }

            $program = $this->programRepo->findById($programId);

            return response()->json(new ProgramResource($program));
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_updating_program'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(int $programId): JsonResponse
    {
        try {
            $success = $this->programRepo->delete($programId);

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.program_not_found'),
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.program_deleted_successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_deleting_program'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
