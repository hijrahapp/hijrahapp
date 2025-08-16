<?php

namespace App\Livewire\Homepage\Shared;

use App\Http\Controllers\App\UserDetailsController;
use App\Http\Services\UserService;
use Livewire\Component;

class ChangePassword extends Component
{
    public $current_password = '';
    public $new_password = '';
    public $confirm_password = '';
    public $error = '';
    public $success = '';

    protected $rules = [
        'current_password' => 'required',
        'new_password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
        'confirm_password' => 'required|same:new_password',
    ];

    protected function messages()
    {
        return [
            'confirm_password.same' => __('messages.password_and_confirmation_mismatch'),
            'new_password.min' => __('messages.invalid_password_format'),
            'new_password.regex' => __('messages.invalid_password_format')
        ];
    }


    protected $listeners = ['reset-modal' => 'resetForm'];

    public function save()
    {
        $this->error = '';
        $this->success = '';

        $this->validate();

        $user = app(UserDetailsController::class)->getAllUserDetails(session('jwt_token'));
        if (isset($user['message'])) {
            $this->error = $user['message'];
        }

        $response = app(UserService::class)->resetPasswordWithCurrent($user, $this->current_password, $this->new_password);
        logger($response);
        if (is_array($response) && isset($response['error'])) {
            $this->error = $response['error'];
        } elseif (is_array($response) && isset($response['message'])) {
            $this->success = $response['message'];
            $this->close();
        } else {
            $this->success = __('messages.password_changed_successfully');
            $this->close();
        }
    }

    public function close()
    {
        $this->dispatch('click');
    }

    public function resetForm()
    {
        $this->resetErrorBag();
        $this->current_password = '';
        $this->new_password = '';
        $this->confirm_password = '';
        $this->error = '';
        $this->success = '';
    }

    public function render()
    {
        return view('livewire.homepage.shared.change-password');
    }
}
