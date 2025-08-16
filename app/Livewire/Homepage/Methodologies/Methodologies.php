<?php

namespace App\Livewire\Homepage\Methodologies;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class Methodologies extends Component
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
        return view('livewire.homepage.methodologies.methodologies');
    }
}
