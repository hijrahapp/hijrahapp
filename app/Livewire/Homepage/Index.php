<?php

namespace App\Livewire\Homepage;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.index')]
class Index extends Component
{
    public function mount()
    {
        // Check if user has a valid session token
        if (!session('jwt_token')) {
            return redirect()->route('login');
        } else {
            $role = session('user')["role"] ?? null;
            if ($role === 'Expert') {
                return redirect()->route('methodologies');
            } elseif ($role === 'SuperAdmin' || $role === 'Admin') {
                return redirect()->route('admins');
            }
        }
    }

    public function render()
    {
        return view('livewire.homepage.index');
    }
}
