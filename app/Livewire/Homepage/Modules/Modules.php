<?php

namespace App\Livewire\Homepage\Modules;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class Modules extends Component
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
        return view('livewire.homepage.modules.modules');
    }
}
