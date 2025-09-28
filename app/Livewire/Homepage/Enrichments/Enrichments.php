<?php

namespace App\Livewire\Homepage\Enrichments;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class Enrichments extends Component
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
        return view('livewire.homepage.enrichments.enrichments');
    }
}
