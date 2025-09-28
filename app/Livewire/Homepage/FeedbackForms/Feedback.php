<?php

namespace App\Livewire\Homepage\FeedbackForms;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class Feedback extends Component
{
    public function mount()
    {
        // Check if user has a valid session token
        if (! session('jwt_token')) {
            return redirect()->route('login');
        }
    }

    public function render()
    {
        return view('livewire.homepage.feedback-forms.feedback');
    }

    public function openFeedbackFormManagement()
    {
        return redirect()->route('feedback-forms');
    }
}
