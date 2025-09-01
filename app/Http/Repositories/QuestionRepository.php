<?php

namespace App\Http\Repositories;

use App\Models\Methodology;
use App\Models\Module;
use App\Models\Pillar;
use App\Models\Question;
use App\Resources\QuestionResource;

class QuestionRepository
{
    /**
     * Get questions by context type and context ID
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
     */
    private function getQuestionsByMethodology(int $methodologyId)
    {
        $methodology = Methodology::find($methodologyId);

        if (! $methodology) {
            throw new \InvalidArgumentException("Methodology with ID {$methodologyId} not found");
        }

        // Get questions with their weights for this methodology, sorted by sequence
        // Using raw query to ensure proper ordering
        $questionIds = \DB::table('methodology_question')
            ->where('methodology_id', $methodologyId)
            ->orderBy('sequence', 'asc')
            ->orderBy('id', 'asc')
            ->pluck('question_id');

        $questions = collect();
        foreach ($questionIds as $questionId) {
            $question = \App\Models\Question::with('answers')->find($questionId);
            if ($question) {
                // Get full pivot data including ID for weights
                $pivotData = \DB::table('methodology_question')
                    ->where('methodology_id', $methodologyId)
                    ->where('question_id', $questionId)
                    ->first();

                $question->setRelation('pivot', (object) [
                    'methodology_id' => $pivotData->methodology_id,
                    'question_id' => $pivotData->question_id,
                    'weight' => $pivotData->weight,
                    'sequence' => $pivotData->sequence,
                ]);

                // Load weights for this question
                if ($pivotData->id) {
                    $weights = \App\Models\AnswerContext::where('context_type', 'methodology_question')
                        ->where('context_id', $pivotData->id)
                        ->get()
                        ->keyBy('answer_id');

                    $question->setAttribute('answer_weights', $weights);
                }

                $questions->push($question);
            }
        }

        return $questions;
    }

    /**
     * Get questions for a specific pillar with weights
     */
    private function getQuestionsByPillar(int $pillarId, ?int $methodologyId = null)
    {
        $pillar = Pillar::find($pillarId);
        if (! $pillar) {
            throw new \InvalidArgumentException("Pillar with ID {$pillarId} not found");
        }

        $modules = $pillar->modulesForMethodology($methodologyId)->get();

        $result = [];
        foreach ($modules as $module) {
            $sequence = null;
            $pm = \DB::table('pillar_module')
                ->where('methodology_id', (int) $methodologyId)
                ->where('pillar_id', (int) $pillarId)
                ->where('module_id', $module->id)
                ->first();
            if ($pm) {
                $pivotDescription = property_exists($pm, 'questions_description') ? $pm->questions_description : null;
                $pivotEstimatedTime = property_exists($pm, 'questions_estimated_time') ? $pm->questions_estimated_time : null;
                // No sequence column in pillar_module; derive order by creation id within this pillar + methodology
                $orderedModuleIds = \DB::table('pillar_module')
                    ->where('methodology_id', (int) $methodologyId)
                    ->where('pillar_id', (int) $pillarId)
                    ->orderBy('id')
                    ->pluck('module_id')
                    ->toArray();
                $position = array_search($module->id, $orderedModuleIds, true);
                if ($position !== false) {
                    $sequence = $position + 1;
                }
            }
            $questions = $this->getQuestionsByModule($module->id, $methodologyId, $pillarId);
            foreach ($questions as $question) {
                $question->setAttribute('module_id', $module->id);
                $question->setAttribute('module_name', $module->name);
                $question->setAttribute('questionId_moduleId', $question->id.'_'.$module->id);
                $question->setAttribute('module_sequence', $sequence);
                $result[] = $question;
            }
        }

        // Sort the final result by sequence within each module
        // Since questions are already sorted by sequence within modules,
        // we need to sort by module order and then by sequence within modules
        usort($result, function ($a, $b) {
            // First sort by module_id to maintain module order
            if ($a->module_id !== $b->module_id) {
                return $a->module_id <=> $b->module_id;
            }
            // Then sort by sequence within the same module
            $aSequence = $a->pivot->sequence ?? 999999;
            $bSequence = $b->pivot->sequence ?? 999999;
            if ($aSequence !== $bSequence) {
                return $aSequence <=> $bSequence;
            }

            // Finally by id as tiebreaker
            return $a->id <=> $b->id;
        });

        return $result;
    }

    /**
     * Get questions for a specific module with weights
     */
    private function getQuestionsByModule(int $moduleId, ?int $methodologyId = null, ?int $pillarId = null)
    {
        $module = Module::find($moduleId);

        if (! $module) {
            throw new \InvalidArgumentException("Module with ID {$moduleId} not found");
        }

        // Get questions with their weights for this module, sorted by sequence
        $query = \DB::table('module_question')->where('module_id', $moduleId);

        if ($methodologyId) {
            $query->where('methodology_id', $methodologyId);
        }
        if ($pillarId) {
            $query->where('pillar_id', $pillarId);
        }

        $questionIds = $query->orderBy('sequence', 'asc')
            ->orderBy('id', 'asc')
            ->pluck('question_id');

        $questions = collect();
        foreach ($questionIds as $questionId) {
            $question = \App\Models\Question::with('answers')->find($questionId);
            if ($question) {
                // Get full pivot data including ID for weights
                $pivotQuery = \DB::table('module_question')
                    ->where('module_id', $moduleId)
                    ->where('question_id', $questionId);

                if ($methodologyId) {
                    $pivotQuery->where('methodology_id', $methodologyId);
                }
                if ($pillarId) {
                    $pivotQuery->where('pillar_id', $pillarId);
                }

                $pivotData = $pivotQuery->first();

                $question->setRelation('pivot', (object) [
                    'module_id' => $pivotData->module_id,
                    'question_id' => $pivotData->question_id,
                    'methodology_id' => $pivotData->methodology_id,
                    'pillar_id' => $pivotData->pillar_id,
                    'weight' => $pivotData->weight,
                    'sequence' => $pivotData->sequence,
                ]);

                // Load weights for this question
                if ($pivotData->id) {
                    $weights = \App\Models\AnswerContext::where('context_type', 'module_question')
                        ->where('context_id', $pivotData->id)
                        ->get()
                        ->keyBy('answer_id');

                    $question->setAttribute('answer_weights', $weights);
                }

                $questions->push($question);
            }
        }

        return $questions;
    }

    /**
     * Attach next_question_id to each answer on the provided questions list based on dependencies
     * stored in answer_contexts. When pillar context flattens module questions and a
     * questionId_moduleId exists, use that composite id for next_question_id.
     *
     * @param  \Illuminate\Support\Collection|array  $questions
     * @param  string  $context  One of: methodology|pillar|module
     * @param  int  $contextId  MethodologyId for methodology, PillarId for pillar, ModuleId for module
     * @param  int|null  $methodologyId  Optional methodology filter for pillar/module
     * @param  int|null  $pillarId  Optional pillar filter for module
     */
    private function attachNextQuestionDependencies($questions, string $context, int $contextId, ?int $methodologyId, ?int $pillarId): void
    {
        if (! $questions) {
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
                if (! $effectiveModuleId) {
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

            if (! $pivotId) {
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
                            ? ($target->question_id.'_'.$target->module_id)
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
                    $answerToNext[(int) $dep->answer_id] = $nextId;
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
                        ->orderBy('id', 'asc')
                        ->pluck('module_id')
                        ->toArray();

                    $index = array_search($currentModuleId, $orderedModuleIds, true);
                    if ($index !== false && isset($orderedModuleIds[$index + 1])) {
                        $nextModuleId = (int) $orderedModuleIds[$index + 1];

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
                            $fallbackCompositeNextId = ((int) $firstQuestionId).'_'.$nextModuleId;
                        }
                    }
                }
            }

            // Attach next_question_id onto each answer model in the relation
            foreach ($question->answers as $ans) {
                $aid = (int) $ans->id;
                if (array_key_exists($aid, $answerToNext)) {
                    $ans->setAttribute('next_question_id', $answerToNext[$aid]);
                } elseif ($fallbackCompositeNextId !== null) {
                    $ans->setAttribute('next_question_id', $fallbackCompositeNextId);
                }
            }
        }
    }
}
