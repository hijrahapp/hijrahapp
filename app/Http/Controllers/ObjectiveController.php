<?php

namespace App\Http\Controllers;

use App\Http\Repositories\ObjectiveRepository;
use App\Models\Objective;
use App\Resources\ObjectiveDetailedResource;
use App\Resources\ObjectiveResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ObjectiveController
{
    public function __construct(private ObjectiveRepository $objectiveRepo) {}

    public function all(): JsonResponse
    {
        try {
            $objectives = $this->objectiveRepo->getAll();

            return response()->json(ObjectiveResource::collection($objectives));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_objectives'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function get(int $objectiveId): JsonResponse
    {
        try {
            $objective = $this->objectiveRepo->findById($objectiveId);

            if (!$objective) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.objective_not_found'),
                ], 404);
            }

            return response()->json(new ObjectiveDetailedResource($objective));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_objective'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getByProgram(int $programId): JsonResponse
    {
        try {
            $objectives = $this->objectiveRepo->findByProgram($programId);

            return response()->json(ObjectiveResource::collection($objectives));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_program_objectives'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getByType(string $type): JsonResponse
    {
        try {
            if (!in_array($type, array_keys(Objective::TYPES))) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.invalid_objective_type'),
                ], 400);
            }

            $objectives = $this->objectiveRepo->getObjectivesByType($type);

            return response()->json(ObjectiveResource::collection($objectives));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_objectives_by_type'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'program_id' => 'required|integer|exists:programs,id',
                'name' => 'required|string|max:255',
                'type' => ['required', 'string', Rule::in(array_keys(Objective::TYPES))],
                'time_to_finish' => 'required|integer|min:1',
                'time_type' => ['required', 'string', Rule::in(array_keys(Objective::TIME_TYPES))],
                'type_specific_data' => 'required|array',
                'order' => 'nullable|integer|min:1',
            ]);

            // Validate type-specific data based on objective type
            $this->validateTypeSpecificData($validated['type'], $validated['type_specific_data']);

            $objective = $this->objectiveRepo->create($validated);

            return response()->json(new ObjectiveResource($objective), 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_creating_objective'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $objectiveId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'program_id' => 'sometimes|integer|exists:programs,id',
                'name' => 'sometimes|string|max:255',
                'type' => ['sometimes', 'string', Rule::in(array_keys(Objective::TYPES))],
                'time_to_finish' => 'sometimes|integer|min:1',
                'time_type' => ['sometimes', 'string', Rule::in(array_keys(Objective::TIME_TYPES))],
                'type_specific_data' => 'sometimes|array',
                'order' => 'sometimes|integer|min:1',
            ]);

            // Validate type-specific data if provided
            if (isset($validated['type_specific_data'])) {
                $objective = $this->objectiveRepo->findById($objectiveId);
                if (!$objective) {
                    return response()->json([
                        'success' => false,
                        'message' => __('messages.objective_not_found'),
                    ], 404);
                }

                $type = $validated['type'] ?? $objective->type;
                $this->validateTypeSpecificData($type, $validated['type_specific_data']);
            }

            $success = $this->objectiveRepo->update($objectiveId, $validated);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.objective_not_found'),
                ], 404);
            }

            $objective = $this->objectiveRepo->findById($objectiveId);
            return response()->json(new ObjectiveResource($objective));
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_updating_objective'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(int $objectiveId): JsonResponse
    {
        try {
            $success = $this->objectiveRepo->delete($objectiveId);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.objective_not_found'),
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.objective_deleted_successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_deleting_objective'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function reorder(Request $request, int $programId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'objective_orders' => 'required|array',
                'objective_orders.*' => 'integer|exists:objectives,id',
            ]);

            $success = $this->objectiveRepo->reorderObjectives($programId, $validated['objective_orders']);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.error_reordering_objectives'),
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.objectives_reordered_successfully'),
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
                'message' => __('messages.error_reordering_objectives'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function duplicate(Request $request, int $objectiveId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'program_id' => 'nullable|integer|exists:programs,id',
            ]);

            $duplicated = $this->objectiveRepo->duplicateObjective(
                $objectiveId, 
                $validated['program_id'] ?? null
            );

            if (!$duplicated) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.objective_not_found'),
                ], 404);
            }

            return response()->json(new ObjectiveResource($duplicated), 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_duplicating_objective'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getStatistics(): JsonResponse
    {
        try {
            $statistics = $this->objectiveRepo->getStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_objective_statistics'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate type-specific data based on objective type
     */
    private function validateTypeSpecificData(string $type, array $data): void
    {
        $rules = match ($type) {
            'journal' => [
                'header' => 'required|string',
            ],
            'article' => [
                'content' => 'required|string',
            ],
            'advice' => [
                'header' => 'required|string',
                'advices' => 'required|array|min:1',
                'advices.*' => 'required|string',
            ],
            'daily_mission' => [
                'header' => 'required|string',
                'content' => 'required|string',
            ],
            'quiz' => [
                'questions' => 'required|array|min:1',
                'questions.*' => 'required|integer|exists:questions,id',
            ],
            'video' => [
                'content_url' => 'required|url',
                'description' => 'required|string',
            ],
            'audio' => [
                'content_url' => 'required|url',
                'description' => 'required|string',
            ],
            'book' => [
                'content_url' => 'required|url',
                'cover_image' => 'nullable|url',
                'description' => 'required|string',
            ],
            'challenge' => [
                'description' => 'required|string',
                'challenges' => 'required|array|min:1',
                'challenges.*' => 'required|string',
            ],
            default => [],
        };

        validator($data, $rules)->validate();
    }
}
