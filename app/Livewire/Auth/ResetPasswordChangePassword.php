<?php

namespace App\Livewire\Auth;

use App\Http\Controllers\App\UserDetailsController;
use App\Http\Services\UserService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class ResetPasswordChangePassword extends Component
{
    public $password = '';
    public $password_confirmation = '';
    public $error = '';

    protected $rules = [
        'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
        'password_confirmation' => 'required|same:password',
    ];

    protected function messages()
    {
        return [
            'password_confirmation.same' => __('messages.password_and_confirmation_mismatch'),
            'password.min' => __('messages.invalid_password_format'),
            'password.regex' => __('messages.invalid_password_format')
        ];
    }

    public function submit()
    {
        $this->validate();
        $this->error = '';
        if ($this->password !== $this->password_confirmation) {
            $this->error = 'Passwords do not match.';
            return;
        }

        $user = app(UserDetailsController::class)->getAllUserDetails(session('jwt_token'));

        if( isset($user['message'])) {
            $this->error = $user['message'];
            return redirect()->route('login');
        }

        $response = app(UserService::class)->resetPassword($user, $this->password);
        if (method_exists($response, 'getStatusCode') && $response->getStatusCode() === 200) {
            session()->forget('jwt_token');
            return redirect()->route('password.changed');
        } else {
            $this->error = $response->getData(true)['message'] ?? 'Change password failed.';
        }
    }

    public function render()
    {
        return view('livewire.auth.reset-password-change-password');
    }
}
