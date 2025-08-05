<?php

namespace App\Livewire\Auth;

use App\Http\Services\OTPService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class ResetPasswordEnterEmail extends Component
{
    public $email = '';
    public $error = '';

    protected $rules = [
        'email' => 'required|email',
    ];
    public function submit()
    {
        $this->validate();
        $this->error = '';
        try {
            $otpService = app(OTPService::class);
            $response = $otpService->resendPasswordOTP($this->email);
            if (method_exists($response, 'getStatusCode') && $response->getStatusCode() === 201) {
                session(['reset_email' => $this->email]);
                return redirect()->route('password.2fa');
            } else {
                $this->error = $response->getData(true)['message'] ?? 'Enter email failed.';
            }
        } catch (\Exception $e) {
            $this->error = 'An error occurred: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.auth.reset-password-enter-email');
    }
}
