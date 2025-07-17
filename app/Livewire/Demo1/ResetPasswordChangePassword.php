<?php

namespace App\Livewire\Demo1;

use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\UserMiddleware;
use App\Http\Services\UserService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.auth')]
class ResetPasswordChangePassword extends Component
{
    public $password = '';
    public $password_confirmation = '';
    public $error = '';

    public function submit()
    {
        $this->error = '';
        if ($this->password !== $this->password_confirmation) {
            $this->error = 'Passwords do not match.';
            return;
        }

        $jwt = session('jwt_token');
        if (!$jwt) {
            $this->error = 'Session expired. Please restart the reset process.';
            return redirect()->route('login');
            return;
        }

        $decodedToken = app(JwtMiddleware::class)->decodeToken($jwt);

        if( isset($decodedToken['message'])) {
            $this->error = $decodedToken['message'];
            return redirect()->route('login');
            return;
        }

        $user = app(UserMiddleware::class)->fetchAndValidateUser($decodedToken['sub']);

        if( isset($user['message'])) {
            $this->error = $user['message'];
            return redirect()->route('login');
        }

        app(UserService::class)->resetPassword($user, $this->password);
        session()->forget('jwt_token');
        return redirect()->route('password.changed');
    }

    public function render()
    {
        return view('livewire.demo1.reset-password-change-password');
    }
}
