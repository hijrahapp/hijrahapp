<?php

namespace App\Livewire\Homepage\Interests;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class Interests extends Component
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
        return view('livewire.homepage.interests.interests');
    }
}
