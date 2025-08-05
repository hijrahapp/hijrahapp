<?php

namespace App\Livewire\Auth;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class ResetPasswordChanged extends Component
{
    public function render()
    {
        return view('livewire.auth.reset-password-changed');
    }
}
