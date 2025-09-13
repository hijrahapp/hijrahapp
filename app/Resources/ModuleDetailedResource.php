<?php

namespace App\Resources;

use App\Http\Repositories\QuestionRepository;
use App\Services\ContextStatusService;
use App\Services\ResultCalculationOptimizedService;
use App\Services\ResultCalculationService;
use App\Traits\HasTagTitles;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class ModuleDetailedResource extends JsonResource
{
    use HasTagTitles;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $payload = [
            'id' => $this->id,
        ];

        $details = [
            'name' => $this->name,
            'description' => $this->description,
            'definition' => $this->definition,
            'objectives' => $this->objectives,
            'imgUrl' => $this->img_url,
            'tags' => $this->getTagTitles($this->tags),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        $payload['details'] = $this->filterArray($details);

        $methodologyId = request()->route('methodologyId');
        $pillarId = $this->pillar_id ?? request()->route('pillarId');
        $sequence = null;

        // Prefer pivot meta when methodologyId (and optionally pillarId) are provided
        $pivotDescription = null;
        $pivotEstimatedTime = null;
        if ($methodologyId) {
            if ($pillarId) {
                $pm = DB::table('pillar_module')
                    ->where('methodology_id', (int) $methodologyId)
                    ->where('pillar_id', (int) $pillarId)
                    ->where('module_id', $this->id)
                    ->first();
                if ($pm) {
                    $pivotDescription = property_exists($pm, 'questions_description') ? $pm->questions_description : null;
                    $pivotEstimatedTime = property_exists($pm, 'questions_estimated_time') ? $pm->questions_estimated_time : null;
                    // No sequence column in pillar_module; derive order by creation id within this pillar + methodology
                    $orderedModuleIds = DB::table('pillar_module')
                        ->where('methodology_id', (int) $methodologyId)
                        ->where('pillar_id', (int) $pillarId)
                        ->orderBy('id')
                        ->pluck('module_id')
                        ->toArray();
                    $position = array_search($this->id, $orderedModuleIds, true);
                    if ($position !== false) {
                        $sequence = $position + 1;
                    }
                }
            } else {
                $mm = DB::table('methodology_module')
                    ->where('methodology_id', (int) $methodologyId)
                    ->where('module_id', $this->id)
                    ->first();
                if ($mm) {
                    $pivotDescription = property_exists($mm, 'questions_description') ? $mm->questions_description : null;
                    $pivotEstimatedTime = property_exists($mm, 'questions_estimated_time') ? $mm->questions_estimated_time : null;
                    // No sequence column in methodology_module; derive order by creation id within this methodology
                    $orderedModuleIds = DB::table('methodology_module')
                        ->where('methodology_id', (int) $methodologyId)
                        ->orderBy('id')
                        ->pluck('module_id')
                        ->toArray();
                    $position = array_search($this->id, $orderedModuleIds, true);
                    if ($position !== false) {
                        $sequence = $position + 1;
                    }
                }
            }
        }
        if ($sequence !== null) {
            $payload['details'] = $this->filterArray(array_merge($payload['details'], ['sequence' => $sequence]));
        }

        $questionsRepo = new QuestionRepository;
        $contextStatusService = new ContextStatusService;
        $questions = [];
        if ($pillarId && $methodologyId) {
            $questions = $questionsRepo->getQuestionsByContext('module', $this->id, $methodologyId, $pillarId);
        } elseif ($methodologyId) {
            $questions = $questionsRepo->getQuestionsByContext('module', $this->id, $methodologyId);
        }
        $questions['description'] = $pivotDescription;
        $questions['estimatedTime'] = $pivotEstimatedTime;
        $questions['size'] = count($questions['list']);
        $questions['status'] = $this->user_id ? $contextStatusService->getModuleStatus($this->user_id, $this->id, $methodologyId, $pillarId) : 'not_started';
        $questions = $this->filterArray($questions);
        unset($questions['list']);
        $payload['questions'] = $questions;

        if (config('app.features.result_calculation')) {
            $result = $this->calculateResult();
            if ($result) {
                $payload['result'] = $result;
            }

            $eligiblePrograms = $this->getEligiblePrograms($request, $questions['status'], $methodologyId, $pillarId, $result);
            if (! empty($eligiblePrograms)) {
                $payload['programs'] = $eligiblePrograms;
            }
        }

        return $payload;
    }

    private function calculateResult()
    {
        $service = config('app.features.optimized_calculation')
            ? new ResultCalculationOptimizedService
            : new ResultCalculationService;
        $methodologyId = request()->route('methodologyId');
        $pillarId = $this->pillar_id ?? request()->route('pillarId');
        if ($this->user_id && $methodologyId) {
            return $service->calculateModuleResult($this->user_id, $this->id, (int) $methodologyId, $pillarId ? (int) $pillarId : null);
        }

        return null;
    }

    /**
     * Get programs that the user is eligible for based on module completion and score
     */
    private function getEligiblePrograms(Request $request, string $moduleStatus, ?string $methodologyId, ?string $pillarId, ?array $result): array
    {
        // Only show eligible programs if user is authenticated and module is completed
        if (! $this->user_id || ! $methodologyId || $moduleStatus !== 'completed' || ! $result || ! isset($result['percentage'])) {
            return [];
        }

        $userScore = $result['percentage'];

        // Query programs where user score falls within min_score and max_score range
        $eligiblePrograms = DB::table('program_module as pm')
            ->join('programs as p', 'pm.program_id', '=', 'p.id')
            ->where('pm.module_id', $this->id)
            ->where('pm.methodology_id', $methodologyId)
            ->where('p.active', true) // Only include active programs
            ->where(function ($query) use ($pillarId) {
                if ($pillarId) {
                    $query->where('pm.pillar_id', $pillarId);
                } else {
                    $query->whereNull('pm.pillar_id');
                }
            })
            ->where('pm.min_score', '<=', $userScore)
            ->where('pm.max_score', '>=', $userScore)
            ->select('p.*', 'pm.min_score', 'pm.max_score')
            ->get();

        // Get steps count for each program separately
        if ($eligiblePrograms->isNotEmpty()) {
            $programIds = $eligiblePrograms->pluck('id')->toArray();
            $stepsCounts = DB::table('steps')
                ->whereIn('program_id', $programIds)
                ->select('program_id', DB::raw('COUNT(*) as steps_count'))
                ->groupBy('program_id')
                ->pluck('steps_count', 'program_id');

            // Add steps_count to each program
            $eligiblePrograms = $eligiblePrograms->map(function ($program) use ($stepsCounts) {
                $program->steps_count = $stepsCounts->get($program->id, 0);

                return $program;
            });
        }

        // Transform to ProgramResource format
        $programs = [];
        foreach ($eligiblePrograms as $program) {
            // Create a new request with the authenticated user ID
            $requestWithAuth = clone $request;
            $requestWithAuth->merge(['authUserId' => $this->user_id]);

            $programResource = new ProgramResource((object) $program);
            $programs[] = $programResource->toArray($requestWithAuth);
        }

        return $programs;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function filterArray(array $data): array
    {
        return array_filter($data, function ($value) {
            if ($value === null) {
                return false;
            }
            if (is_string($value) && trim($value) === '') {
                return false;
            }
            if (is_array($value) && count($value) === 0) {
                return false;
            }

            return true;
        });
    }
}
