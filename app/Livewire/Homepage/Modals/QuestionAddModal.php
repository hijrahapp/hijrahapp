<?php

namespace App\Livewire\Homepage\Modals;

use App\Enums\QuestionType;
use App\Models\Question;
// use App\Models\Tag; // No longer directly used; tag handling moved to shared component
use Livewire\Attributes\Computed;
use Livewire\Component;

class QuestionAddModal extends Component
{
    public $title = '';
    public $type = '';
    public $tags = [];
    public $customAnswers = [];
    public $newAnswer = '';
    public $error = '';
    // Tag suggestions and creation are handled in the shared TagPicker component
    public $isEditMode = false;
    public $questionId = null;

    protected function rules()
    {
        $rules = [
            'title' => 'required|string|min:3',
            'type' => 'required|string',
            'tags' => 'array',
            'tags.*' => 'required|integer',
        ];

        // Only validate customAnswers for MCQ question types
        if ($this->type && in_array($this->type, ['MCQSingle', 'MCQMultiple'])) {
            $rules['customAnswers'] = 'required|array|min:2';
            $rules['customAnswers.*'] = 'required|string|min:1';
        }

        return $rules;
    }

    protected function messages()
    {
        return [
            'title.required' => 'Question title is required.',
            'title.min' => 'Question title must be at least 3 characters.',
            'type.required' => 'Question type is required.',
            'tags.*.required' => 'Tag is required.',
            'tags.*.integer' => 'Tag must be a valid ID.',
            'customAnswers.required' => 'At least 2 answers are required for MCQ questions.',
            'customAnswers.min' => 'At least 2 answers are required for MCQ questions.',
            'customAnswers.*.required' => 'Answer cannot be empty.',
            'customAnswers.*.min' => 'Answer must be at least 1 character.',
        ];
    }

    protected $listeners = [
        'reset-modal' => 'resetForm',
        'edit-question' => 'editQuestion'
    ];

    public function mount()
    {
        $this->resetForm();
    }

    #[Computed]
    public function getTypeProperty()
    {
        return $this->type;
    }

    public function setTypeProperty($value)
    {
        $this->type = $value;
        $this->customAnswers = [];
        if ($this->type && QuestionType::from($this->type)->requiresCustomAnswers()) {
            // Initialize with 2 empty answers for MCQ questions
            $this->customAnswers = ['', ''];
        }
    }

    // Tag input events removed

    public function editQuestion($questionId)
    {
        $this->isEditMode = true;
        $this->questionId = $questionId;
        
        $question = Question::findOrFail($questionId);
        
        // Load question data
        $this->title = $question->title;
        $this->type = $question->type->value; // Convert enum to string
        // Tags are stored as an array of IDs on the model
        $this->tags = $question->tags ?? [];
        
        // Load custom answers for MCQ questions
        if ($question->type->requiresCustomAnswers()) {
            $this->customAnswers = $question->answers->pluck('title')->toArray();
        }

        $this->dispatch('show-modal', selector: '#question_add_modal');
    }

    // All tag manipulation is handled by the shared TagPicker component

    public function addAnswer()
    {
        if (!empty(trim($this->newAnswer))) {
            $answer = trim($this->newAnswer);
            if (!in_array($answer, $this->customAnswers)) {
                $this->customAnswers[] = $answer;
            }
            $this->newAnswer = '';
        }
    }

    public function removeAnswer($index)
    {
        if (isset($this->customAnswers[$index])) {
            unset($this->customAnswers[$index]);
            $this->customAnswers = array_values($this->customAnswers);
        }
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->isEditMode) {
                // Update existing question
                $question = Question::findOrFail($this->questionId);
                $question->update([
                    'title' => $this->title,
                    'type' => $this->type,
                    'tags' => $this->tags,
                ]);

                // Remove all existing answers and reattach
                $question->answers()->detach();
            } else {
                // Create new question
                $question = Question::create([
                    'title' => $this->title,
                    'type' => $this->type,
                    'tags' => $this->tags,
                ]);
            }

            // Handle answers based on question type
            $questionType = QuestionType::from($this->type);
            
            if ($questionType->requiresCustomAnswers()) {
                // For MCQ questions, create custom answers
                foreach ($this->customAnswers as $answerTitle) {
                    $answer = \App\Models\Answer::firstOrCreate(['title' => $answerTitle]);
                    $question->answers()->attach($answer->id);
                }
            } else {
                // For predefined answer types, attach the predefined answers
                $predefinedAnswers = $questionType->getAnswers();
                foreach ($predefinedAnswers as $answerTitle) {
                    $answer = \App\Models\Answer::firstOrCreate(['title' => $answerTitle]);
                    $question->answers()->attach($answer->id);
                }
            }

            $this->dispatch('refreshTable');
            $this->closeModal();
        } catch (\Exception $e) {
            $this->error = 'Failed to ' . ($this->isEditMode ? 'update' : 'create') . ' question: ' . $e->getMessage();
        }
    }

    public function resetForm()
    {
        $this->resetErrorBag();
        $this->title = '';
        $this->type = '';
        $this->tags = [];
        // Tags remain bound via shared component
        $this->customAnswers = [];
        $this->newAnswer = '';
        $this->error = '';
        $this->isEditMode = false;
        $this->questionId = null;
    }

    public function closeModal()
    {
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

    public function getPredefinedAnswers()
    {
        if (!$this->type) return [];
        
        $questionType = QuestionType::from($this->type);
        return $questionType->getAnswers();
    }

    // Selected tags list no longer needed in the view

    // Normalization no longer needed; shared TagPicker provides numeric IDs

    public function render()
    {
        return view('livewire.homepage.modals.question-add-modal', [
            'questionTypes' => $this->getQuestionTypes(),
            'predefinedAnswers' => $this->getPredefinedAnswers(),
        ]);
    }
}
