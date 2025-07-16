<?php

namespace App\Livewire\Demo1;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;
use App\Http\Services\OTPService;

#[Layout('layouts.auth')]
class ResetPasswordEnterEmail extends Component
{
    public $email = '';
    public $error = '';

    public function submit()
    {
        $this->error = '';
        try {
            $otpService = app(OTPService::class);
            $otpService->resendPasswordOTP($this->email);
            session(['reset_email' => $this->email]);
            return redirect()->route('password.2fa');
        } catch (\Exception $e) {
            $this->error = 'An error occurred: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.demo1.reset-password-enter-email');
    }
} 