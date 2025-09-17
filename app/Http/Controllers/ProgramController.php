<?php

namespace App\Http\Controllers;

use App\Http\Repositories\ProgramRepository;
use App\Http\Requests\SubmitProgramFeedbackRequest;
use App\Models\FeedbackForm;
use App\Models\Program;
use App\Models\ProgramFeedback;
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

            $programs = $this->programRepo->getSuggestedPrograms(
                $user->id,
                array_filter($methodologyIds),
                array_filter($moduleIds),
                array_filter($status)
            );

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

            $programs = $this->programRepo->getUserPrograms(
                $user->id,
                array_filter($methodologyIds),
                array_filter($moduleIds),
                array_filter($status)
            );

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

    public function resetProgram(Request $request, int $programId): JsonResponse
    {
        try {
            $user = $request->authUser;
            $success = $this->programRepo->resetProgram($user->id, $programId);

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.program_not_found_or_not_started'),
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.program_reset_successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_resetting_program'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSuggestedProgramsFilters(Request $request): JsonResponse
    {
        try {
            $user = $request->authUser;
            $filters = $this->programRepo->getSuggestedProgramsFilters($user->id);

            return response()->json($filters);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_filters'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getMyProgramsFilters(Request $request): JsonResponse
    {
        try {
            $user = $request->authUser;
            $filters = $this->programRepo->getUserProgramsFilters($user->id);

            return response()->json($filters);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_filters'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function submitFeedback(SubmitProgramFeedbackRequest $request, int $programId): JsonResponse
    {
        try {
            $user = $request->authUser;

            // Check if program exists and user completed it
            Program::findOrFail($programId);
            $userProgram = $user->programs()->wherePivot('program_id', $programId)->first();

            if (! $userProgram || $userProgram->pivot->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.program_not_completed_cannot_submit_feedback'),
                ], 400);
            }

            // Create or update feedback
            $feedback = ProgramFeedback::updateOrCreate(
                ['user_id' => $user->id, 'program_id' => $programId],
                [
                    'responses' => $request->validated('responses'),
                    'form_version' => $request->validated('form_version', '1.0'),
                    'submitted_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => __('messages.feedback_submitted_successfully'),
                'data' => [
                    'feedback_id' => $feedback->id,
                    'submitted_at' => $feedback->submitted_at->toISOString(),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_submitting_feedback'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getFeedbackStats(int $programId): JsonResponse
    {
        try {
            $program = Program::findOrFail($programId);

            $stats = ProgramFeedback::where('program_id', $programId)
                ->selectRaw("
                    COUNT(*) as total_responses,
                    AVG(JSON_EXTRACT(responses, '$.overall_rating')) as avg_overall_rating,
                    COUNT(CASE WHEN JSON_EXTRACT(responses, '$.life_improvement') = 'yes' THEN 1 END) as life_improvement_yes,
                    COUNT(CASE WHEN JSON_EXTRACT(responses, '$.life_improvement') = 'somewhat' THEN 1 END) as life_improvement_somewhat,
                    COUNT(CASE WHEN JSON_EXTRACT(responses, '$.life_improvement') = 'no' THEN 1 END) as life_improvement_no,
                    COUNT(CASE WHEN JSON_EXTRACT(responses, '$.content_clarity') = 'excellent' THEN 1 END) as content_excellent,
                    COUNT(CASE WHEN JSON_EXTRACT(responses, '$.content_clarity') = 'good' THEN 1 END) as content_good,
                    COUNT(CASE WHEN JSON_EXTRACT(responses, '$.content_clarity') = 'needs_improvement' THEN 1 END) as content_needs_improvement
                ")
                ->first();

            // Get most popular content types
            $contentTypes = ProgramFeedback::where('program_id', $programId)
                ->get()
                ->flatMap(function ($feedback) {
                    return $feedback->getMostBeneficialContent();
                })
                ->countBy()
                ->sortDesc()
                ->take(5);

            return response()->json([
                'success' => true,
                'data' => [
                    'program_name' => $program->name,
                    'total_responses' => (int) $stats->total_responses,
                    'average_rating' => round($stats->avg_overall_rating, 2),
                    'life_improvement' => [
                        'yes' => (int) $stats->life_improvement_yes,
                        'somewhat' => (int) $stats->life_improvement_somewhat,
                        'no' => (int) $stats->life_improvement_no,
                    ],
                    'content_clarity' => [
                        'excellent' => (int) $stats->content_excellent,
                        'good' => (int) $stats->content_good,
                        'needs_improvement' => (int) $stats->content_needs_improvement,
                    ],
                    'popular_content_types' => $contentTypes->toArray(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_feedback_stats'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getFeedbackForm(Request $request): JsonResponse
    {
        try {
            $language = $request->input('language', 'ar');
            $version = $request->input('version');

            // Get form by version or active form
            $form = $version
                ? FeedbackForm::getByVersion($version, $language)
                : FeedbackForm::getActiveForm($language);

            if (! $form) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.feedback_form_not_found'),
                ], 404);
            }

            return response()->json([
                    'version' => $form->version,
                    'name' => $form->name,
                    'description' => $form->description,
                    'form_structure' => $form->form_structure,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_feedback_form'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
