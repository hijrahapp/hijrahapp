<?php

namespace App\Livewire\Homepage\Programs;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class Programs extends Component
{
    public function mount()
    {
        if (! session('jwt_token')) {
            return redirect()->route('login');
        }
    }

    public function render()
    {
        return view('livewire.homepage.programs.programs');
    }

    public function openFeedbackFormManagement()
    {
        return redirect()->route('feedback-forms');
    }
}
