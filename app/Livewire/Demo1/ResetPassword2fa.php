<?php

namespace App\Livewire\Demo1;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.auth')]
class ResetPassword2fa extends Component
{
    public $email = '';
    public $otp1 = '';
    public $otp2 = '';
    public $otp3 = '';
    public $otp4 = '';
    public $error = '';

    public function mount()
    {
        $this->email = session('reset_email', '');
        // Optionally clear it after use:
    }

    public function submit()
    {
        $this->error = '';
        $otp = $this->otp1 . $this->otp2 . $this->otp3 . $this->otp4;
        if (strlen($otp) !== 4) {
            $this->error = 'OTP must be 4 digits.';
            return;
        }
        // dd($otp); // Print the concatenated OTP string
        session()->forget('reset_email');
    }

    public function render()
    {
        return view('livewire.demo1.reset-password-2fa');
    }
} 