<?php

namespace App\Livewire\Homepage\Methodologies\MethodologyQuestions;

use App\Enums\QuestionType;
use App\Models\Methodology;
use App\Models\Question;
use Livewire\Component;

class MethodologyQuestionAddModal extends Component
{
    public bool $isEditMode = false;
    public int $editingQuestionId = 0;
    public string $selectedQuestionTitle = '';
    public string $selectedQuestionType = '';
    public array $selectedQuestionAnswers = [];
    public array $questionWeights = [];
    public array $answerWeights = [];
    public int $selectedPillarId = 0;
    public int $selectedModuleId = 0;
    public array $availableQuestions = [];
    public string $error = '';
    public int $methodologyId;

    protected $listeners = [
        'show-modal' => 'showModal',
        'reset-modal' => 'resetForm',
    ];



    public function mount($methodologyId, $editingQuestionId = 0)
    {
        $this->methodologyId = $methodologyId;
        $this->editingQuestionId = $editingQuestionId;
        $this->loadAvailableQuestions();

        // If editing question ID is provided, load the question data
        if ($editingQuestionId > 0) {
            $this->editQuestion($editingQuestionId);
        }
    }

    public function loadAvailableQuestions()
    {
        $methodology = Methodology::with('questions')->find($this->methodologyId);
        if ($methodology) {
            $this->availableQuestions = Question::where('active', true)
                ->whereNotIn('id', $methodology->questions->pluck('id'))
                ->get(['id', 'title', 'type'])
                ->toArray();
        }
    }

    public function showModal($data = [])
    {
        if (isset($data['editingQuestionId'])) {
            $this->editQuestion($data['editingQuestionId']);
        } else {
            $this->resetForm();
        }
    }

    public function editQuestion($questionId)
    {
        $methodology = Methodology::with(['questions.answers'])->find($this->methodologyId);
        $question = $methodology->questions()->with('answers')->find($questionId);

        if (!$question) {
            $this->error = 'Question not found!';
            return;
        }

        $this->isEditMode = true;
        $this->editingQuestionId = $questionId;
        $this->selectedQuestionTitle = $question->title;
        $this->selectedQuestionType = $question->type->value;
        $this->selectedQuestionAnswers = $question->answers->pluck('title')->toArray();

        // Load existing weights from pivot table
        $pivotData = \DB::table('methodology_question')
            ->where('methodology_id', $this->methodologyId)
            ->where('question_id', $questionId)
            ->first();

        $this->questionWeights = [$questionId => $pivotData->weight ?? 100.0];

        // Load answer weights - distribute evenly if not set
        $this->answerWeights = [];
        $answerCount = count($question->answers);
        if ($answerCount > 0) {
            $weightPerAnswer = 100.0 / $answerCount;
            foreach ($question->answers as $answer) {
                $this->answerWeights[$answer->id] = $weightPerAnswer;
            }
        }

        // Load pillar/module assignment
        $pillarQuestion = $question->pillars()->where('methodology_id', $this->methodologyId)->first();
        $moduleQuestion = $question->modules()->where('methodology_id', $this->methodologyId)->first();

        if ($pillarQuestion) {
            $this->selectedPillarId = $pillarQuestion->id;
        } elseif ($moduleQuestion) {
            $this->selectedModuleId = $moduleQuestion->id;
        }
    }

    public function selectQuestion($questionId)
    {
        $question = Question::with('answers')->find($questionId);
        if (!$question) {
            $this->error = 'Question not found!';
            return;
        }

        $this->selectedQuestionTitle = $question->title;
        $this->selectedQuestionType = $question->type->value;
        $this->selectedQuestionAnswers = $question->answers->pluck('title')->toArray();

        // Initialize weights
        $this->questionWeights = ['new' => 100.0]; // Default to 100% for single question
        $this->answerWeights = [];

        // Distribute answer weights evenly
        $answerCount = count($question->answers);
        if ($answerCount > 0) {
            $weightPerAnswer = 100.0 / $answerCount;
            foreach ($question->answers as $answer) {
                $this->answerWeights[$answer->id] = $weightPerAnswer;
            }
        }

        $this->error = '';
    }

    public function save()
    {
        // Validation
        if (empty($this->selectedQuestionTitle)) {
            $this->error = 'Please select a question!';
            return;
        }

        if (empty($this->questionWeights)) {
            $this->error = 'Please set question weight!';
            return;
        }

        if (empty($this->answerWeights)) {
            $this->error = 'Please set answer weights!';
            return;
        }

        // Validate question weight is 100% (for single question)
        $questionWeightSum = array_sum($this->questionWeights);
        if (abs($questionWeightSum - 100) > 0.01) {
            $this->error = 'Question weight must be 100%!';
            return;
        }

        // Validate answer weights sum to 100%
        $answerWeightSum = array_sum($this->answerWeights);
        if (abs($answerWeightSum - 100) > 0.01) {
            $this->error = 'Answer weights must sum to 100%!';
            return;
        }

        try {
            $question = Question::where('title', $this->selectedQuestionTitle)->first();
            if (!$question) {
                $this->error = 'Selected question not found!';
                return;
            }

            $methodology = Methodology::find($this->methodologyId);
            if (!$methodology) {
                $this->error = 'Methodology not found!';
                return;
            }

            if ($this->isEditMode) {
                // Update existing question
                $this->updateQuestion($methodology, $question);
            } else {
                // Add new question
                $this->addQuestion($methodology, $question);
            }

            $this->closeModal();
            $this->dispatch('refreshTable');
            $this->dispatch('show-toast', type: 'success', message: $this->isEditMode ? 'Question updated successfully!' : 'Question added successfully!');
        } catch (\Exception $e) {
            $this->error = 'Error saving question: ' . $e->getMessage();
        }
    }

    private function addQuestion($methodology, $question)
    {
        // Get the next sequence number using raw query
        $maxSequence = \DB::table('methodology_question')
            ->where('methodology_id', $methodology->id)
            ->max('sequence') ?? 0;
        $nextSequence = $maxSequence + 1;

        // Attach question to methodology
        $methodology->questions()->attach($question->id, [
            'weight' => $this->questionWeights['new'] ?? 100.0,
            'sequence' => $nextSequence
        ]);

        // Attach to pillar or module if selected
        if ($this->selectedPillarId) {
            $pillar = $methodology->pillars()->find($this->selectedPillarId);
            if ($pillar) {
                $pillar->questions()->attach($question->id, [
                    'methodology_id' => $this->methodologyId,
                    'weight' => $this->questionWeights['new'] ?? 100.0
                ]);
            }
        } elseif ($this->selectedModuleId) {
            $module = $methodology->modules()->find($this->selectedModuleId);
            if ($module) {
                $module->questions()->attach($question->id, [
                    'methodology_id' => $this->methodologyId,
                    'pillar_id' => null,
                    'weight' => $this->questionWeights['new'] ?? 100.0
                ]);
            }
        }
    }

    private function updateQuestion($methodology, $question)
    {
        // Update question weight
        $methodology->questions()->updateExistingPivot($question->id, [
            'weight' => $this->questionWeights[$this->editingQuestionId] ?? 100.0
        ]);

        // Update pillar/module assignment
        if ($this->selectedPillarId) {
            // Remove from modules first
            $question->modules()->wherePivot('methodology_id', $this->methodologyId)->detach();

            // Update pillar assignment
            $pillar = $methodology->pillars()->find($this->selectedPillarId);
            if ($pillar) {
                $pillar->questions()->updateExistingPivot($question->id, [
                    'methodology_id' => $this->methodologyId,
                    'weight' => $this->questionWeights[$this->editingQuestionId] ?? 100.0
                ]);
            }
        } elseif ($this->selectedModuleId) {
            // Remove from pillars first
            $question->pillars()->wherePivot('methodology_id', $this->methodologyId)->detach();

            // Update module assignment
            $module = $methodology->modules()->find($this->selectedModuleId);
            if ($module) {
                $module->questions()->updateExistingPivot($question->id, [
                    'methodology_id' => $this->methodologyId,
                    'pillar_id' => null,
                    'weight' => $this->questionWeights[$this->editingQuestionId] ?? 100.0
                ]);
            }
        }
    }

    public function resetForm()
    {
        $this->isEditMode = false;
        $this->editingQuestionId = 0;
        $this->selectedQuestionTitle = '';
        $this->selectedQuestionType = '';
        $this->selectedQuestionAnswers = [];
        $this->questionWeights = [];
        $this->answerWeights = [];
        $this->selectedPillarId = 0;
        $this->selectedModuleId = 0;
        $this->error = '';
        $this->loadAvailableQuestions();
    }

    public function closeModal()
    {
        $this->resetForm();
        $this->dispatch('modal-closed');
        $this->dispatch('click');
    }

    public function getQuestionTypes()
    {
        return collect(QuestionType::cases())->map(function ($type) {
            return [
                'value' => $type->value,
                'label' => $type->getLabel(),
            ];
        });
    }

    public function render()
    {
        $methodology = Methodology::with(['pillars', 'modules'])->find($this->methodologyId);

        return view('livewire.homepage.methodologies.methodologyQuestions.methodology-question-add-modal', [
            'methodology' => $methodology,
            'questionTypes' => $this->getQuestionTypes(),
        ]);
    }
}
