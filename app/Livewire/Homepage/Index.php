<?php

namespace App\Livewire\Homepage;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.homepage')]
class Index extends Component
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
        return view('livewire.homepage.index');
    }
}
