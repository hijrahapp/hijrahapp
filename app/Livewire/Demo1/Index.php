<?php

namespace App\Livewire\Demo1;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.demo1.base')]
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
        return view('livewire.demo1.index');
    }
}
