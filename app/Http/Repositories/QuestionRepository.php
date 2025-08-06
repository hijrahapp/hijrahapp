<?php

namespace App\Http\Repositories;

use App\Models\Question;
use App\Models\Methodology;
use App\Models\Pillar;
use App\Models\Module;
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
     * @return Collection
     */
    public function getQuestionsByContext(string $context, int $contextId, ?int $methodologyId = null, ?int $pillarId = null): Collection
    {
        switch ($context) {
            case 'methodology':
                return $this->getQuestionsByMethodology($contextId);
            case 'pillar':
                return $this->getQuestionsByPillar($contextId, $methodologyId);
            case 'module':
                return $this->getQuestionsByModule($contextId, $methodologyId, $pillarId);
            default:
                throw new \InvalidArgumentException("Invalid context type: {$context}");
        }
    }

    /**
     * Get questions for a specific methodology with weights
     * 
     * @param int $methodologyId
     * @return Collection
     */
    private function getQuestionsByMethodology(int $methodologyId): Collection
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
                $weights = \App\Models\QuestionAnswerWeight::where('context_type', 'methodology_question')
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
     * @return Collection
     */
    private function getQuestionsByPillar(int $pillarId, ?int $methodologyId = null): Collection
    {
        $pillar = Pillar::with(['questions.answers'])->find($pillarId);
        
        if (!$pillar) {
            throw new \InvalidArgumentException("Pillar with ID {$pillarId} not found");
        }

        // Get questions with their weights for this pillar
        $questions = $methodologyId 
            ? $pillar->questionsForMethodology($methodologyId)->get()
            : $pillar->questions;
        
        // Load weights for each question in this pillar context
        foreach ($questions as $question) {
            $pivotId = \DB::table('pillar_question')
                ->where('pillar_id', $pillarId)
                ->where('question_id', $question->id)
                ->when($methodologyId, function($query) use ($methodologyId) {
                    return $query->where('methodology_id', $methodologyId);
                })
                ->value('id');
                
            if ($pivotId) {
                $weights = \App\Models\QuestionAnswerWeight::where('context_type', 'pillar_question')
                    ->where('context_id', $pivotId)
                    ->get()
                    ->keyBy('answer_id');
                    
                $question->setAttribute('answer_weights', $weights);
            }
        }

        return $questions;
    }

    /**
     * Get questions for a specific module with weights
     * 
     * @param int $moduleId
     * @param int|null $methodologyId
     * @param int|null $pillarId
     * @return Collection
     */
    private function getQuestionsByModule(int $moduleId, ?int $methodologyId = null, ?int $pillarId = null): Collection
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
                $weights = \App\Models\QuestionAnswerWeight::where('context_type', 'module_question')
                    ->where('context_id', $pivotId)
                    ->get()
                    ->keyBy('answer_id');
                    
                $question->setAttribute('answer_weights', $weights);
            }
        }

        return $questions;
    }

    /**
     * Get all questions with their answers
     * 
     * @return Collection
     */
    public function getAllWithAnswers(): Collection
    {
        return Question::with('answers')->get();
    }

    /**
     * Get a specific question with its answers
     * 
     * @param int $questionId
     * @return Question|null
     */
    public function findByIdWithAnswers(int $questionId): ?Question
    {
        return Question::with('answers')->find($questionId);
    }
} 