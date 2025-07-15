<?php

namespace App\Livewire\Demo1;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;
use App\Http\Services\AuthService;

#[Layout('layouts.auth')]
class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;
    public $error = '';

    public function login()
    {
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
        $this->email = '';
        $this->password = '';
        $this->error = '';
    }

    public function render()
    {
        return view('livewire.demo1.login');
    }
} 