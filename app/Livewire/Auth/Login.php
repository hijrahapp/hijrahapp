<?php

namespace App\Livewire\Auth;

use App\Http\Services\AuthService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;
    public $error = '';

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function login()
    {
        $this->validate();
        $this->error = '';
        try {
            $authService = app(AuthService::class);
            logger($this->email);
            logger($this->password);
            $response = $authService->adminLogin($this->email, $this->password);
            logger($response);
            if (method_exists($response, 'getStatusCode') && $response->getStatusCode() === 200) {
                $data = $response->getData(true);
                if (isset($data['access_token'])) {
                    session(['jwt_token' => $data['access_token']]);
                    session(['user' => $data['user'] ?? null]);
                    return redirect()->intended('/');
                } else {
                    $this->error = 'Token not found in response.';
                }
            } else {
                $this->error = $response->getData(true)['message'] ?? 'Login failed.';
            }
        } catch (\Exception $e) {
            $this->error = 'An error occurred: ' . $e->getMessage();
        }
    }

    public function mount()
    {
        if (session('jwt_token')) {
            return redirect()->intended('/');
        }
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
