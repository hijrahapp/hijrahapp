<?php

namespace App\Livewire\Demo1;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Http\Services\OTPService;

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
        $otpService = app(OTPService::class);
        $response = $otpService->verifyPasswordOTP($this->email, $otp);
        if (method_exists($response, 'getStatusCode') && $response->getStatusCode() === 200) {
            $data = $response->getData(true);
            session(['jwt_token' => $data['access_token'] ?? null]);
            session()->forget('reset_email');
            return redirect()->route('password.reset');
        } else {
            $this->error = $response->getData(true)['message'] ?? 'OTP verification failed.';
        }
    }

    public function render()
    {
        return view('livewire.demo1.reset-password-2fa');
    }
} 