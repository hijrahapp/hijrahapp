<?php

namespace App\Livewire\Homepage\Categories;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class Categories extends Component
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
        return view('livewire.homepage.categories.categories');
    }
}
