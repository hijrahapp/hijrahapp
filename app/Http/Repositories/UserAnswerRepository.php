<?php

namespace App\Http\Repositories;

use App\Models\UserAnswer;
use App\Models\User;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserAnswerRepository
{
    /**
     * Submit user answers for methodology questions
     *
     * @param int $userId
     * @param int $methodologyId
     * @param array $answers
     * @return SupportCollection
     */
    public function submitMethodologyAnswers(int $userId, int $methodologyId, array $answers): SupportCollection
    {
        $submittedAnswers = $this->submitAnswers($userId, 'methodology', $methodologyId, $answers);
        return $submittedAnswers->groupBy('question_id');
    }

    /**
     * Submit user answers for pillar questions within a methodology
     *
     * @param int $userId
     * @param int $methodologyId
     * @param int $pillarId
     * @param array $answers
     * @return SupportCollection
     */
    public function submitPillarAnswers(int $userId, int $methodologyId, int $pillarId, array $answers): SupportCollection
    {
        $submittedAnswers = $this->submitAnswers($userId, 'pillar', $pillarId, $answers, $methodologyId);
        return $submittedAnswers->groupBy('question_id');
    }

    /**
     * Submit user answers for module questions within a methodology
     *
     * @param int $userId
     * @param int $methodologyId
     * @param int $moduleId
     * @param array $answers
     * @return SupportCollection
     */
    public function submitModuleAnswers(int $userId, int $methodologyId, int $moduleId, array $answers): SupportCollection
    {
        $submittedAnswers = $this->submitAnswers($userId, 'module', $moduleId, $answers, $methodologyId);
        return $submittedAnswers->groupBy('question_id');
    }

    /**
     * Submit user answers for module questions within a pillar of a methodology
     *
     * @param int $userId
     * @param int $methodologyId
     * @param int $pillarId
     * @param int $moduleId
     * @param array $answers
     * @return SupportCollection
     */
    public function submitPillarModuleAnswers(int $userId, int $methodologyId, int $pillarId, int $moduleId, array $answers): SupportCollection
    {
        $submittedAnswers = $this->submitAnswers($userId, 'module', $moduleId, $answers, $methodologyId, $pillarId);
        return $submittedAnswers->groupBy('question_id');
    }

    /**
     * Generic method to submit user answers
     *
     * @param int $userId
     * @param string $contextType
     * @param int $contextId
     * @param array $answers
     * @param int|null $methodologyId
     * @param int|null $pillarId
     * @return SupportCollection
     */
    private function submitAnswers(int $userId, string $contextType, int $contextId, array $answers, ?int $methodologyId = null, ?int $pillarId = null): SupportCollection
    {
        // Validate user exists
        $user = User::find($userId);
        if (!$user) {
            throw new \InvalidArgumentException("User with ID {$userId} not found");
        }

        // Validate context exists
        $this->validateContext($contextType, $contextId, $methodologyId, $pillarId);

        // Validate answers
        $this->validateAnswers($answers);

        $submittedAnswers = collect();

        DB::transaction(function () use ($userId, $contextType, $contextId, $answers, $methodologyId, $pillarId, &$submittedAnswers) {
            // Delete existing answers for this context
            $this->deleteExistingAnswers($userId, $contextType, $contextId, $methodologyId, $pillarId);

            // Create new answers
            foreach ($answers as $answerData) {
                $questionId = $answerData['question_id'];

                // Create UserAnswer record for each answer ID
                foreach ($answerData['answerIds'] as $answerId) {
                    $userAnswer = UserAnswer::create([
                        'user_id' => $userId,
                        'context_type' => $contextType,
                        'context_id' => $contextId,
                        'question_id' => $questionId,
                        'answer_id' => $answerId,
                    ]);

                    $submittedAnswers->push($userAnswer->load(['question', 'answer']));
                }
            }
        });

        return $submittedAnswers;
    }

    /**
     * Validate the context exists
     *
     * @param string $contextType
     * @param int $contextId
     * @param int|null $methodologyId
     * @param int|null $pillarId
     * @return void
     */
    private function validateContext(string $contextType, int $contextId, ?int $methodologyId = null, ?int $pillarId = null): void
    {
        switch ($contextType) {
            case 'methodology':
                $methodology = \App\Models\Methodology::find($contextId);
                if (!$methodology) {
                    throw new \InvalidArgumentException("Methodology with ID {$contextId} not found");
                }
                break;
            case 'pillar':
                $pillar = \App\Models\Pillar::find($contextId);
                if (!$pillar) {
                    throw new \InvalidArgumentException("Pillar with ID {$contextId} not found");
                }
                if ($methodologyId) {
                    $methodology = \App\Models\Methodology::find($methodologyId);
                    if (!$methodology) {
                        throw new \InvalidArgumentException("Methodology with ID {$methodologyId} not found");
                    }
                }
                break;
            case 'module':
                $module = \App\Models\Module::find($contextId);
                if (!$module) {
                    throw new \InvalidArgumentException("Module with ID {$contextId} not found");
                }
                if ($methodologyId) {
                    $methodology = \App\Models\Methodology::find($methodologyId);
                    if (!$methodology) {
                        throw new \InvalidArgumentException("Methodology with ID {$methodologyId} not found");
                    }
                }
                break;
            default:
                throw new \InvalidArgumentException("Invalid context type: {$contextType}");
        }
    }

    /**
     * Validate answers data
     *
     * @param array $answers
     * @return void
     */
    private function validateAnswers(array $answers): void
    {
        if (empty($answers)) {
            throw new \InvalidArgumentException("Answers array cannot be empty");
        }

        foreach ($answers as $answer) {
            if (!isset($answer['question_id'])) {
                throw new \InvalidArgumentException("Each answer must have question_id");
            }

            if (!isset($answer['answerIds'])) {
                throw new \InvalidArgumentException("Each answer must have answerIds array");
            }

            if (!is_array($answer['answerIds'])) {
                throw new \InvalidArgumentException("answerIds must be an array");
            }

            if (empty($answer['answerIds'])) {
                throw new \InvalidArgumentException("answerIds array cannot be empty");
            }

            // Validate question exists
            $question = Question::find($answer['question_id']);
            if (!$question) {
                throw new \InvalidArgumentException("Question with ID {$answer['question_id']} not found");
            }

            // Validate that MCQMultiple questions can have multiple answers, others only single
            if ($question->type->value !== 'MCQMultiple' && count($answer['answerIds']) > 1) {
                throw new \InvalidArgumentException("Question {$answer['question_id']} does not support multiple answers");
            }

            // Validate each answer ID
            foreach ($answer['answerIds'] as $answerId) {
                $answerModel = Answer::find($answerId);
                if (!$answerModel) {
                    throw new \InvalidArgumentException("Answer with ID {$answerId} not found");
                }

                // Validate answer belongs to question
                if (!$question->answers()->where('answer_id', $answerId)->exists()) {
                    throw new \InvalidArgumentException("Answer {$answerId} does not belong to question {$answer['question_id']}");
                }
            }
        }
    }

    /**
     * Delete existing answers for the given context
     *
     * @param int $userId
     * @param string $contextType
     * @param int $contextId
     * @param int|null $methodologyId
     * @param int|null $pillarId
     * @return void
     */
    private function deleteExistingAnswers(int $userId, string $contextType, int $contextId, ?int $methodologyId = null, ?int $pillarId = null): void
    {
        $query = UserAnswer::where('user_id', $userId)
            ->where('context_type', $contextType)
            ->where('context_id', $contextId);

        // For module context, we need to consider methodology and pillar
        if ($contextType === 'module' && $methodologyId && $pillarId) {
            // Get question IDs for this specific module in this pillar of this methodology
            $questionIds = \App\Models\Module::find($contextId)
                ->questionsForPillarInMethodology($methodologyId, $pillarId)
                ->pluck('questions.id');

            $query->whereIn('question_id', $questionIds);
        } elseif ($contextType === 'module' && $methodologyId) {
            // Get question IDs for this module in this methodology
            $questionIds = \App\Models\Module::find($contextId)
                ->questionsForMethodology($methodologyId)
                ->pluck('questions.id');

            $query->whereIn('question_id', $questionIds);
        } elseif ($contextType === 'pillar' && $methodologyId) {
            // Get question IDs for this pillar in this methodology
            $questionIds = \App\Models\Pillar::find($contextId)
                ->questionsForMethodology($methodologyId)
                ->pluck('questions.id');

            $query->whereIn('question_id', $questionIds);
        } elseif ($contextType === 'methodology') {
            // Get question IDs for this methodology
            $questionIds = \App\Models\Methodology::find($contextId)
                ->questions()
                ->pluck('questions.id');

            $query->whereIn('question_id', $questionIds);
        }

        $query->delete();
    }

    /**
     * Get user answers for a specific context
     *
     * @param int $userId
     * @param string $contextType
     * @param int $contextId
     * @param int|null $methodologyId
     * @param int|null $pillarId
     * @return Collection
     */
    public function getUserAnswers(int $userId, string $contextType, int $contextId, ?int $methodologyId = null, ?int $pillarId = null): Collection
    {
        $query = UserAnswer::with(['question', 'answer'])
            ->where('user_id', $userId)
            ->where('context_type', $contextType)
            ->where('context_id', $contextId);

        // Apply additional filters based on context
        if ($contextType === 'module' && $methodologyId && $pillarId) {
            $questionIds = \App\Models\Module::find($contextId)
                ->questionsForPillarInMethodology($methodologyId, $pillarId)
                ->pluck('questions.id');

            $query->whereIn('question_id', $questionIds);
        } elseif ($contextType === 'module' && $methodologyId) {
            $questionIds = \App\Models\Module::find($contextId)
                ->questionsForMethodology($methodologyId)
                ->pluck('questions.id');

            $query->whereIn('question_id', $questionIds);
        } elseif ($contextType === 'pillar' && $methodologyId) {
            $userAnswers = new Collection();

            $pillar = \App\Models\Pillar::find($contextId);
            $modules = $pillar->modulesForMethodology($methodologyId)->get();
            foreach ($modules as $module) {
                $moduleQuestionIds = $module->questionsForPillarInMethodology($methodologyId, $contextId)
                    ->pluck('questions.id');

                Log::info($module->id);

                $newQuery = UserAnswer::with(['question', 'answer'])
                ->where('user_id', $userId)
                ->where('context_type', 'module')
                ->where('context_id', $module->id)
                ->whereIn('question_id', $moduleQuestionIds);
                $queryResult = $newQuery->get();

                foreach ($queryResult as $result) {
                    $result->setAttribute('module_id', $module->id);
                    $result->setAttribute('module_name', $module->name);
                    $userAnswers->push($result);
                }
            }

            return $userAnswers;
        } elseif ($contextType === 'methodology') {
            $questionIds = \App\Models\Methodology::find($contextId)
                ->questions()
                ->pluck('questions.id');

            $query->whereIn('question_id', $questionIds);
        }

        return $query->get();
    }

    /**
     * Get user answers grouped by question for a specific context
     *
     * @param int $userId
     * @param string $contextType
     * @param int $contextId
     * @param int|null $methodologyId
     * @param int|null $pillarId
     * @return Collection
     */
    public function getUserAnswersGrouped(int $userId, string $contextType, int $contextId, ?int $methodologyId = null, ?int $pillarId = null)
    {
        $answers = $this->getUserAnswers($userId, $contextType, $contextId, $methodologyId, $pillarId);

        // Group answers by question_id
        return $answers->groupBy(function ($item) {
            return $item->question_id . '_' . $item->context_id;
        });
    }
}
