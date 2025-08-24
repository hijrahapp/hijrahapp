<?php

namespace App\Http\Repositories;

use App\Models\Question;
use App\Models\Methodology;
use App\Models\Pillar;
use App\Models\Module;
use App\Resources\QuestionResource;
use Illuminate\Database\Eloquent\Collection;

class QuestionRepository
{
    /**
     * Get questions by context type and context ID
     *
     * @param string $context
     * @param int $contextId
     * @param int|null $methodologyId
     * @param int|null $pillarId
     */
    public function getQuestionsByContext(string $context, int $contextId, ?int $methodologyId = null, ?int $pillarId = null)
    {
        $data = [];
        $questions = null;

        switch ($context) {
            case 'methodology':
                $questions = $this->getQuestionsByMethodology($contextId);
                break;
            case 'pillar':
                $questions = $this->getQuestionsByPillar($contextId, $methodologyId);
                break;
            case 'module':
                $questions = $this->getQuestionsByModule($contextId, $methodologyId, $pillarId);
                break;
            default:
                throw new \InvalidArgumentException("Invalid context type: {$context}");
        }

        if (config('app.features.dynamic_questions')) {
            // Enrich answers with next_question_id when dependencies are configured
            $this->attachNextQuestionDependencies($questions, $context, $contextId, $methodologyId, $pillarId);

            $type = 'simple';
            foreach ($questions as $question) {
                foreach ($question->answers as $answer) {
                    if ($answer->next_question_id) {
                        $type = 'dynamic';
                        break 2;
                    }
                }
            }

            $data['type'] = $type;
        }

        $data['list'] = QuestionResource::collection($questions);
        return $data;
    }

    /**
     * Get questions for a specific methodology with weights
     *
     * @param int $methodologyId
     */
    private function getQuestionsByMethodology(int $methodologyId)
    {
        $methodology = Methodology::with(['questions.answers'])->find($methodologyId);

        if (!$methodology) {
            throw new \InvalidArgumentException("Methodology with ID {$methodologyId} not found");
        }

        // Get questions with their weights for this methodology
        $questions = $methodology->questions;

        // Load weights for each question in this methodology context
        foreach ($questions as $question) {
            $pivotId = \DB::table('methodology_question')
                ->where('methodology_id', $methodologyId)
                ->where('question_id', $question->id)
                ->value('id');

            if ($pivotId) {
                $weights = \App\Models\AnswerContext::where('context_type', 'methodology_question')
                    ->where('context_id', $pivotId)
                    ->get()
                    ->keyBy('answer_id');

                $question->setAttribute('answer_weights', $weights);
            }
        }

        return $questions;
    }

    /**
     * Get questions for a specific pillar with weights
     *
     * @param int $pillarId
     * @param int|null $methodologyId
     */
    private function getQuestionsByPillar(int $pillarId, ?int $methodologyId = null)
    {
        $pillar = Pillar::find($pillarId);
        if (!$pillar) {
            throw new \InvalidArgumentException("Pillar with ID {$pillarId} not found");
        }

        $modules = $pillar->modulesForMethodology($methodologyId)->get();

        $result = [];
        foreach ($modules as $module) {
            $questions = $this->getQuestionsByModule($module->id, $methodologyId, $pillarId);
            foreach ($questions as $question) {
                $question->setAttribute('module_id', $module->id);
                $question->setAttribute('module_name', $module->name);
                $question->setAttribute('questionId_moduleId', $question->id . '_' . $module->id);
                $result[] = $question;
            }
        }

        return $result;
    }

    /**
     * Get questions for a specific module with weights
     *
     * @param int $moduleId
     * @param int|null $methodologyId
     * @param int|null $pillarId
     */
    private function getQuestionsByModule(int $moduleId, ?int $methodologyId = null, ?int $pillarId = null)
    {
        $module = Module::with(['questions.answers'])->find($moduleId);

        if (!$module) {
            throw new \InvalidArgumentException("Module with ID {$moduleId} not found");
        }

        // Get questions with their weights for this module
        if ($methodologyId && $pillarId) {
            $questions = $module->questionsForPillarInMethodology($methodologyId, $pillarId)->get();
        } elseif ($methodologyId) {
            $questions = $module->questionsForMethodology($methodologyId)->get();
        } else {
            $questions = $module->questions;
        }

        // Load weights for each question in this module context
        foreach ($questions as $question) {
            $pivotId = \DB::table('module_question')
                ->where('module_id', $moduleId)
                ->where('question_id', $question->id)
                ->when($methodologyId, function($query) use ($methodologyId) {
                    return $query->where('methodology_id', $methodologyId);
                })
                ->when($pillarId, function($query) use ($pillarId) {
                    return $query->where('pillar_id', $pillarId);
                })
                ->value('id');

            if ($pivotId) {
                $weights = \App\Models\AnswerContext::where('context_type', 'module_question')
                    ->where('context_id', $pivotId)
                    ->get()
                    ->keyBy('answer_id');

                $question->setAttribute('answer_weights', $weights);
            }
        }

        return $questions;
    }

    /**
     * Attach next_question_id to each answer on the provided questions list based on dependencies
     * stored in answer_contexts. When pillar context flattens module questions and a
     * questionId_moduleId exists, use that composite id for next_question_id.
     *
     * @param \Illuminate\Support\Collection|array $questions
     * @param string $context One of: methodology|pillar|module
     * @param int $contextId MethodologyId for methodology, PillarId for pillar, ModuleId for module
     * @param int|null $methodologyId Optional methodology filter for pillar/module
     * @param int|null $pillarId Optional pillar filter for module
     */
    private function attachNextQuestionDependencies($questions, string $context, int $contextId, ?int $methodologyId, ?int $pillarId): void
    {
        if (!$questions) {
            return;
        }

        $isPillarContext = ($context === 'pillar');
        $effectivePillarId = $pillarId;
        if ($isPillarContext) {
            // In pillar context, $contextId is the pillar id
            $effectivePillarId = $contextId;
        }

        foreach ($questions as $question) {
            // Determine pivot row and context type
            if ($context === 'methodology') {
                $pivotTable = 'methodology_question';
                $contextType = 'methodology_question';
                $pivotId = \DB::table($pivotTable)
                    ->where('methodology_id', $contextId)
                    ->where('question_id', $question->id)
                    ->value('id');
            } else {
                // module or pillar contexts use module_question
                $pivotTable = 'module_question';
                $contextType = 'module_question';
                $effectiveModuleId = ($context === 'module') ? $contextId : ($question->module_id ?? null);
                if (!$effectiveModuleId) {
                    continue;
                }
                $pivotQuery = \DB::table($pivotTable)
                    ->where('module_id', $effectiveModuleId)
                    ->where('question_id', $question->id);
                if ($methodologyId) {
                    $pivotQuery->where('methodology_id', $methodologyId);
                }
                if ($effectivePillarId) {
                    $pivotQuery->where('pillar_id', $effectivePillarId);
                }
                $pivotId = $pivotQuery->value('id');
            }

            if (!$pivotId) {
                continue;
            }

            // Fetch dependencies for this question's answers in the current context
            $deps = \DB::table('answer_contexts')
                ->where('context_type', $contextType)
                ->where('context_id', $pivotId)
                ->whereNotNull('dependent_context_type')
                ->whereNotNull('dependent_context_id')
                ->get(['answer_id', 'dependent_context_type', 'dependent_context_id']);

            // Build mapping answer_id => next_question_identifier
            $answerToNext = [];
            foreach ($deps as $dep) {
                $nextId = null;
                if ($dep->dependent_context_type === 'module_question') {
                    $target = \DB::table('module_question')
                        ->where('id', $dep->dependent_context_id)
                        ->first(['question_id', 'module_id']);
                    if ($target) {
                        $nextId = $isPillarContext
                            ? ($target->question_id . '_' . $target->module_id)
                            : $target->question_id;
                    }
                } elseif ($dep->dependent_context_type === 'methodology_question') {
                    $target = \DB::table('methodology_question')
                        ->where('id', $dep->dependent_context_id)
                        ->first(['question_id']);
                    if ($target) {
                        $nextId = $target->question_id;
                    }
                }

                if ($nextId !== null) {
                    $answerToNext[(int)$dep->answer_id] = $nextId;
                }
            }

            // Pillar context fallback: if an answer has no explicit next, point to
            // the first question of the next module in the same pillar (unless last module)
            $fallbackCompositeNextId = null;
            if ($isPillarContext && $methodologyId && $effectivePillarId) {
                $currentModuleId = $effectiveModuleId ?? null;
                if ($currentModuleId) {
                    $orderedModuleIds = \DB::table('pillar_module')
                        ->where('methodology_id', $methodologyId)
                        ->where('pillar_id', $effectivePillarId)
                        ->orderBy('created_at', 'asc')
                        ->pluck('module_id')
                        ->toArray();

                    $index = array_search($currentModuleId, $orderedModuleIds, true);
                    if ($index !== false && isset($orderedModuleIds[$index + 1])) {
                        $nextModuleId = (int)$orderedModuleIds[$index + 1];

                        $q = \DB::table('module_question')
                            ->where('module_id', $nextModuleId);
                        if ($methodologyId) {
                            $q->where('methodology_id', $methodologyId);
                        }
                        if ($effectivePillarId) {
                            $q->where('pillar_id', $effectivePillarId);
                        }

                        // Prefer sequence if available, then id as tie-breaker
                        if (\Schema::hasColumn('module_question', 'sequence')) {
                            $q->orderBy('sequence', 'asc');
                        }
                        $firstQuestionId = $q->orderBy('id', 'asc')->value('question_id');

                        if ($firstQuestionId) {
                            $fallbackCompositeNextId = ((int)$firstQuestionId) . '_' . $nextModuleId;
                        }
                    }
                }
            }

            // Attach next_question_id onto each answer model in the relation
            foreach ($question->answers as $ans) {
                $aid = (int)$ans->id;
                if (array_key_exists($aid, $answerToNext)) {
                    $ans->setAttribute('next_question_id', $answerToNext[$aid]);
                } elseif ($fallbackCompositeNextId !== null) {
                    $ans->setAttribute('next_question_id', $fallbackCompositeNextId);
                }
            }
        }
    }
}
