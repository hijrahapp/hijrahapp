<?php

namespace App\Http\Repositories;

use App\Models\Methodology;

class MethodologyRepository
{
    public function getAllBasic() {
        return Methodology::where('active', true)->get();
    }

    public function getAll() {
        return Methodology::with(['pillars', 'modules', 'questions'])->get();
    }

    public function findById(int $methodologyId): ?Methodology {
        return Methodology::with(['pillars', 'modules', 'questions'])->find($methodologyId);
    }

    public function findByIdWithFullDetails(int $methodologyId): ?Methodology {
        $methodology = Methodology::with([
            'pillars.modules.questions.answers',
            'pillars.questions.answers',
            'modules.questions.answers',
            'questions.answers'
        ])->find($methodologyId);

        if ($methodology) {
            $this->loadWeightsForMethodology($methodology);
        }

        return $methodology;
    }

    /**
     * Find a methodology with a specific pillar and its nested relations
     */
    public function findByIdWithSpecificPillar(int $methodologyId, int $pillarId): ?Methodology {
        $methodology = Methodology::with([
            'pillars' => function ($query) use ($pillarId) {
                $query->where('pillars.id', $pillarId);
            },
            'pillars.modules.questions.answers',
            'pillars.questions.answers',
            'questions.answers',
        ])->find($methodologyId);

        if ($methodology) {
            $this->loadWeightsForMethodology($methodology);
        }

        return $methodology;
    }

    /**
     * Find a methodology with a specific direct module and its nested relations
     */
    public function findByIdWithSpecificModule(int $methodologyId, int $moduleId): ?Methodology {
        $methodology = Methodology::with([
            'modules' => function ($query) use ($moduleId) {
                $query->where('modules.id', $moduleId);
            },
            'modules.questions.answers',
            'questions.answers',
        ])->find($methodologyId);

        if ($methodology) {
            $this->loadWeightsForMethodology($methodology);
        }

        return $methodology;
    }

    /**
     * Find a methodology with a specific pillar and specific module under that pillar
     */
    public function findByIdWithSpecificPillarModule(int $methodologyId, int $pillarId, int $moduleId): ?Methodology {
        $methodology = Methodology::with([
            'pillars' => function ($query) use ($pillarId) {
                $query->where('pillars.id', $pillarId);
            },
            'pillars.modules' => function ($query) use ($moduleId) {
                $query->where('modules.id', $moduleId);
            },
            'pillars.modules.questions.answers',
            'pillars.questions.answers',
            'questions.answers',
        ])->find($methodologyId);

        if ($methodology) {
            $this->loadWeightsForMethodology($methodology);
        }

        return $methodology;
    }

    public function findByIdWithSectionPillars(int $methodologyId, int $sectionNumber): ?Methodology {
        $methodology = Methodology::with([
            'pillars' => function ($query) use ($sectionNumber) {
                $query->where('section', $sectionNumber === 1 ? 'first' : 'second');
            },
            'pillars.modules.questions.answers',
            'pillars.questions.answers',
            'modules.questions.answers',
            'questions.answers'
        ])->find($methodologyId);

        if ($methodology) {
            $this->loadWeightsForMethodology($methodology);
        }

        return $methodology;
    }

    public function create(array $data): Methodology {
        return Methodology::create($data);
    }

    public function update(int $methodologyId, array $data): bool {
        $methodology = Methodology::find($methodologyId);
        if (!$methodology) {
            return false;
        }

        return $methodology->update($data);
    }

    public function delete($methodology): bool {
        return $methodology->delete();
    }

    /**
     * Load weights for all questions in a methodology (methodology questions, pillar questions, module questions)
     */
    private function loadWeightsForMethodology(Methodology $methodology): void
    {
        // Load weights for methodology questions
        foreach ($methodology->questions as $question) {
            $this->loadWeightsForQuestion($question, 'methodology_question', $methodology->id);
        }

        // Load weights for pillar questions
        foreach ($methodology->pillars as $pillar) {
            foreach ($pillar->questions as $question) {
                $this->loadWeightsForQuestion($question, 'pillar_question', $pillar->id);
            }

            // Load weights for module questions within pillars
            foreach ($pillar->modules as $module) {
                foreach ($module->questions as $question) {
                    $this->loadWeightsForQuestion($question, 'module_question', $module->id);
                }
            }
        }

        // Load weights for direct module questions (not within pillars)
        foreach ($methodology->modules as $module) {
            foreach ($module->questions as $question) {
                $this->loadWeightsForQuestion($question, 'module_question', $module->id);
            }
        }
    }

    /**
     * Load weights for a specific question in a specific context
     */
    private function loadWeightsForQuestion($question, string $contextType, int $contextId): void
    {
        $pivotId = \DB::table($contextType)
            ->where($this->getContextIdColumn($contextType), $contextId)
            ->where('question_id', $question->id)
            ->value('id');

        if ($pivotId) {
            $weights = \App\Models\QuestionAnswerWeight::where('context_type', $contextType)
                ->where('context_id', $pivotId)
                ->get()
                ->keyBy('answer_id');

            $question->setAttribute('answer_weights', $weights);
        }
    }

    /**
     * Get the context ID column name based on context type
     */
    private function getContextIdColumn(string $contextType): string
    {
        return match ($contextType) {
            'methodology_question' => 'methodology_id',
            'pillar_question' => 'pillar_id',
            'module_question' => 'module_id',
            default => throw new \InvalidArgumentException("Invalid context type: {$contextType}")
        };
    }
} 