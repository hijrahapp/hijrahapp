<?php

namespace App\Livewire\Demo1;

use App\Http\Controllers\App\UserDetailsController;
use App\Http\Services\UserService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.auth')]
class ResetPasswordChangePassword extends Component
{
    public $password = '';
    public $password_confirmation = '';
    public $error = '';

    protected $rules = [
        'password' => 'required',
        'password_confirmation' => 'required|same:password',
    ];

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
        return view('livewire.demo1.reset-password-change-password');
    }
}
