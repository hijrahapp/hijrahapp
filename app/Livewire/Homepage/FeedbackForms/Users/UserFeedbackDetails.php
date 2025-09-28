<?php

namespace App\Livewire\Homepage\FeedbackForms\Users;

use App\Models\FeedbackForm;
use App\Models\Program;
use App\Models\ProgramFeedback;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class UserFeedbackDetails extends Component
{
    public Program $program;

    public User $user;

    public ?ProgramFeedback $feedback = null;

    public ?FeedbackForm $feedbackForm = null;

    public array $questions = [];

    public array $responses = [];

    public function mount(Program $program, User $user)
    {
        // Check if user has a valid session token
        if (! session('jwt_token')) {
            return redirect()->route('login');
        }

        $this->program = $program;
        $this->user = $user;

        // Load the feedback submission
        $this->feedback = ProgramFeedback::where('program_id', $program->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $this->feedback) {
            $this->dispatch('show-toast', type: 'error', message: 'Feedback not found.');

            return redirect()->route('program.feedback.users', ['program' => $program]);
        }

        // Load the feedback form structure
        $this->feedbackForm = FeedbackForm::where('version', $this->feedback->form_version)
            ->first();

        if ($this->feedbackForm && isset($this->feedbackForm->form_structure['questions'])) {
            $this->questions = $this->feedbackForm->form_structure['questions'];
        }

        // Load responses
        $this->responses = $this->feedback->responses ?? [];
    }

    public function getResponseValue(string|int $questionKey, mixed $default = null): mixed
    {
        // Try multiple key formats since responses can be stored with string or integer keys
        $possibleKeys = [
            $questionKey,                    // Original key
            (string) $questionKey,           // String version
            (int) $questionKey,              // Integer version
        ];

        foreach ($possibleKeys as $key) {
            if (array_key_exists($key, $this->responses)) {
                return $this->responses[$key];
            }
        }

        return $default;
    }

    public function getFormattedResponseValue(array $question, mixed $value): string
    {
        if (is_null($value) || $value === '') {
            return 'No answer provided';
        }

        switch ($question['type']) {
            case 'rating':
                return "{$value} / {$question['max_value']} stars";

            case 'single_choice':
                // Find the option label if it exists
                if (isset($question['options'])) {
                    foreach ($question['options'] as $option) {
                        if (is_array($option) && ($option['value'] === $value)) {
                            return $option['label'] ?? $value;
                        } elseif ($option === $value) {
                            return $option;
                        }
                    }
                }

                return $value;

            case 'multiple_choice':
                if (is_array($value)) {
                    $labels = [];
                    foreach ($value as $selectedValue) {
                        if (isset($question['options'])) {
                            foreach ($question['options'] as $option) {
                                if (is_array($option) && ($option['value'] === $selectedValue)) {
                                    $labels[] = $option['label'] ?? $selectedValue;
                                    break;
                                } elseif ($option === $selectedValue) {
                                    $labels[] = $option;
                                    break;
                                }
                            }
                        } else {
                            $labels[] = $selectedValue;
                        }
                    }

                    return implode(', ', $labels);
                }

                return $value;

            case 'text':
                return $value;

            default:
                return $value;
        }
    }

    public function backToUserList()
    {
        return redirect()->route('program.feedback.users', ['program' => $this->program]);
    }

    public function render()
    {
        return view('livewire.homepage.feedback-forms.users.user-feedback-details');
    }
}
