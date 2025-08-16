<?php

namespace App\Livewire\Homepage\Pillars;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class Pillars extends Component
{
    public function mount()
    {
        // Check if user has a valid session token
        if (!session('jwt_token')) {
            return redirect()->route('login');
        }
    }

    public function render()
    {
        return view('livewire.homepage.pillars.pillars');
    }
}
