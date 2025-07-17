<?php

namespace App\Livewire\Demo1;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.auth')]
class ResetPasswordChanged extends Component
{
    public function render()
    {
        return view('livewire.demo1.reset-password-changed');
    }
} 