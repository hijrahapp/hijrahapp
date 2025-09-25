<?php

namespace App\Livewire\Homepage\FeedbackForms;

use App\Models\FeedbackForm;
use App\Models\ProgramFeedback;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.index')]
class FeedbackFormManagement extends Component
{
    use WithPagination;

    // Form creation properties
    public $formTitle = '';

    public $formVersion = '';

    public $formDescription = '';

    public $formLanguage = 'ar';

    // Question builder properties
    public $questionText = '';

    public $questionType = '';

    public $questionRequired = false;

    public $ratingMin = 1;

    public $ratingMax = 5;

    public $questionOptions = [];

    public $questionMaxLength = 500;

    // Questions list
    public $questions = [];

    // Change tracking
    public $hasChanges = false;

    public $originalFormData = [];

    public function mount()
    {
        // Check if user has a valid session token
        if (! session('jwt_token')) {
            return redirect()->route('login');
        }

        $this->loadLatestForm();
        $this->saveOriginalFormData();
    }

    public function loadLatestForm(): void
    {
        $latestForm = FeedbackForm::where('language', $this->formLanguage)
            ->orderByRaw('CAST(SUBSTRING_INDEX(version, ".", 1) AS UNSIGNED) DESC, CAST(SUBSTRING_INDEX(version, ".", -1) AS UNSIGNED) DESC')
            ->first();

        if ($latestForm) {
            // Load form details
            $this->formTitle = $latestForm->name;
            $this->formDescription = $latestForm->description ?? '';
            $this->formVersion = $latestForm->version;

            // Load form structure questions
            $formStructure = $latestForm->form_structure;
            if (isset($formStructure['questions']) && is_array($formStructure['questions'])) {
                $this->questions = $formStructure['questions'];
            }
        }
    }

    public function addQuestion(): void
    {
        // Validate question form using Livewire rules
        $this->validateQuestionForm();

        $question = [
            'key' => count($this->questions),
            'text' => $this->questionText,
            'type' => $this->questionType,
            'required' => $this->questionRequired,
        ];

        switch ($this->questionType) {
            case 'rating':
                $question['min_value'] = $this->ratingMin;
                $question['max_value'] = $this->ratingMax;
                break;
            case 'single_choice':
            case 'multiple_choice':
                $question['options'] = array_map(function ($option) {
                    return ['value' => $option, 'label' => $option];
                }, array_filter($this->questionOptions, fn ($opt) => ! empty(trim($opt))));
                break;
            case 'text':
                $question['max_length'] = $this->questionMaxLength;
                break;
        }

        $this->questions[] = $question;
        $this->resetQuestionForm();
        $this->checkForChanges();
        $this->dispatch('show-toast', type: 'success', message: 'Question added successfully!');
    }

    public function removeQuestion($index): void
    {
        unset($this->questions[$index]);
        $this->questions = array_values($this->questions);

        // Re-index the questions
        foreach ($this->questions as $key => $question) {
            $this->questions[$key]['key'] = $key;
        }

        $this->checkForChanges();
        $this->dispatch('show-toast', type: 'success', message: 'Question removed successfully!');
    }

    public function moveQuestionUp($index): void
    {
        if ($index > 0) {
            $temp = $this->questions[$index];
            $this->questions[$index] = $this->questions[$index - 1];
            $this->questions[$index - 1] = $temp;

            // Re-index the questions
            foreach ($this->questions as $key => $question) {
                $this->questions[$key]['key'] = $key;
            }

            $this->checkForChanges();
        }
    }

    public function moveQuestionDown($index): void
    {
        if ($index < count($this->questions) - 1) {
            $temp = $this->questions[$index];
            $this->questions[$index] = $this->questions[$index + 1];
            $this->questions[$index + 1] = $temp;

            // Re-index the questions
            foreach ($this->questions as $key => $question) {
                $this->questions[$key]['key'] = $key;
            }

            $this->checkForChanges();
        }
    }

    public function resetQuestionForm(): void
    {
        $this->questionText = '';
        $this->questionType = '';
        $this->questionRequired = false;
        $this->ratingMin = 1;
        $this->ratingMax = 5;
        $this->questionOptions = [];
        $this->questionMaxLength = 500;
    }

    public function saveForm(): void
    {
        try {
            $this->validate([
                'formTitle' => 'required|string|max:255',
                'formVersion' => 'required|string',
                'formDescription' => 'nullable|string|max:1000',
                'questions' => 'required|array|min:1',
            ], [
                'formTitle.required' => 'Form title is required.',
                'formVersion.required' => 'Form version is required.',
                'questions.required' => 'At least one question is required.',
                'questions.min' => 'At least one question is required.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            $this->dispatch('show-toast', type: 'error', message: implode(' ', $errors));

            return;
        }

        // Check if version already exists
        $existingForm = FeedbackForm::where('version', $this->formVersion)
            ->where('language', $this->formLanguage)
            ->first();

        if ($existingForm) {
            $this->addError('formVersion', 'This version already exists for the selected language.');
            $this->dispatch('show-toast', type: 'error', message: 'This version already exists for the selected language.');

            return;
        }

        // Validate that the new version is greater than the current highest version
        $latestForm = FeedbackForm::where('language', $this->formLanguage)
            ->orderByRaw('CAST(SUBSTRING_INDEX(version, ".", 1) AS UNSIGNED) DESC, CAST(SUBSTRING_INDEX(version, ".", -1) AS UNSIGNED) DESC')
            ->first();

        if ($latestForm && ! $this->isVersionGreater($this->formVersion, $latestForm->version)) {
            $this->addError('formVersion', 'The new version must be greater than the current highest version ('.$latestForm->version.').');
            $this->dispatch('show-toast', type: 'error', message: 'The new version must be greater than the current highest version ('.$latestForm->version.').');

            return;
        }

        // Validate that at least one required rating question exists
        $hasRequiredRatingQuestion = false;
        foreach ($this->questions as $question) {
            if ($question['type'] === 'rating' && ($question['required'] ?? false)) {
                $hasRequiredRatingQuestion = true;
                break;
            }
        }

        if (! $hasRequiredRatingQuestion) {
            $this->addError('questions', 'At least one required rating question is required for the feedback form.');
            $this->dispatch('show-toast', type: 'error', message: 'At least one required rating question is required for the feedback form.');

            return;
        }

        $formStructure = [
            'title' => $this->formTitle,
            'description' => $this->formDescription,
            'questions' => $this->questions,
        ];

        FeedbackForm::create([
            'version' => $this->formVersion,
            'name' => $this->formTitle,
            'form_structure' => $formStructure,
            'language' => $this->formLanguage,
            'description' => $this->formDescription,
        ]);

        $this->resetQuestionForm();
        $this->saveOriginalFormData();
        $this->hasChanges = false;
        $this->dispatch('show-toast', type: 'success', message: 'Feedback form saved successfully!');
    }

    #[On('list-updated')]
    public function handleListUpdate($identifier = null, $items = []): void
    {
        if ($identifier === 'questionOptions') {
            $this->questionOptions = $items;
        }
    }

    public function saveOriginalFormData(): void
    {
        $this->originalFormData = [
            'formTitle' => $this->formTitle,
            'formVersion' => $this->formVersion,
            'formDescription' => $this->formDescription,
            'questions' => $this->questions,
        ];
    }

    public function checkForChanges(): void
    {
        $currentData = [
            'formTitle' => $this->formTitle,
            'formVersion' => $this->formVersion,
            'formDescription' => $this->formDescription,
            'questions' => $this->questions,
        ];

        $this->hasChanges = $currentData !== $this->originalFormData;
    }

    public function updatedFormTitle(): void
    {
        $this->checkForChanges();
    }

    public function updatedFormVersion(): void
    {
        $this->checkForChanges();
    }

    public function updatedFormDescription(): void
    {
        $this->checkForChanges();
    }

    private function isVersionGreater(string $newVersion, string $currentVersion): bool
    {
        $newParts = explode('.', $newVersion);
        $currentParts = explode('.', $currentVersion);

        $newMajor = (int) ($newParts[0] ?? 0);
        $newMinor = (int) ($newParts[1] ?? 0);
        $currentMajor = (int) ($currentParts[0] ?? 0);
        $currentMinor = (int) ($currentParts[1] ?? 0);

        if ($newMajor > $currentMajor) {
            return true;
        }

        if ($newMajor === $currentMajor && $newMinor > $currentMinor) {
            return true;
        }

        return false;
    }

    private function validateQuestionForm(): void
    {
        // Base validation rules
        $rules = [
            'questionText' => 'required|string|max:500',
            'questionType' => 'required|string|in:text,rating,single_choice,multiple_choice',
        ];

        $messages = [
            'questionText.required' => 'Question text is required.',
            'questionText.max' => 'Question text cannot exceed 500 characters.',
            'questionType.required' => 'Question type is required.',
            'questionType.in' => 'Invalid question type selected.',
        ];

        // Add type-specific validation rules
        switch ($this->questionType) {
            case 'rating':
                $rules['ratingMin'] = 'required|integer|min:1';
                $rules['ratingMax'] = 'required|integer|min:2|gt:ratingMin';
                $messages['ratingMin.required'] = 'Minimum rating value is required for rating questions.';
                $messages['ratingMin.min'] = 'Minimum rating must be at least 1.';
                $messages['ratingMax.required'] = 'Maximum rating value is required for rating questions.';
                $messages['ratingMax.min'] = 'Maximum rating must be at least 2.';
                $messages['ratingMax.gt'] = 'Maximum rating must be greater than minimum rating.';
                break;

            case 'single_choice':
            case 'multiple_choice':
                $rules['questionOptions'] = [
                    'required',
                    'array',
                    'min:2',
                    function ($attribute, $value, $fail) {
                        $validOptions = array_filter($value, fn ($opt) => ! empty(trim($opt)));
                        if (count($validOptions) < 2) {
                            $fail('At least 2 non-empty options are required for choice questions.');
                        }
                    },
                ];
                $messages['questionOptions.required'] = 'Options are required for choice questions.';
                $messages['questionOptions.min'] = 'At least 2 options are required for choice questions.';
                break;

            case 'text':
                $rules['questionMaxLength'] = 'required|integer|min:1|max:2000';
                $messages['questionMaxLength.required'] = 'Maximum length is required for text questions.';
                $messages['questionMaxLength.min'] = 'Maximum length must be at least 1 character.';
                $messages['questionMaxLength.max'] = 'Maximum length cannot exceed 2000 characters.';
                break;
        }

        try {
            $this->validate($rules, $messages);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            $this->dispatch('show-toast', type: 'error', message: implode(' ', $errors));

            return;
        }
    }

    public function backToList()
    {
        return redirect()->route('programs');
    }

    public function render()
    {
        return view('livewire.homepage.feedback-forms.feedback-form-management');
    }
}
