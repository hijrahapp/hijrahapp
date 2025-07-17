<?php

namespace App\Livewire\Shared\Modals;

use App\Http\Controllers\App\UserDetailsController;
use App\Http\Services\UserService;
use Livewire\Component;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class ChangePassword extends Component
{
    public $current_password = '';
    public $new_password = '';
    public $confirm_password = '';
    public $error = '';
    public $success = '';

    public function save()
    {
        $this->error = '';
        $this->success = '';

        $this->validate([
            'current_password' => ['required'],
            'new_password' => ['required'],
            'confirm_password' => ['required', 'same:new_password'],
        ], [
            'confirm_password.same' => 'The confirmation password does not match the new password.',
        ]);

        $user = app(UserDetailsController::class)->getAllUserDetails(session('jwt_token'));
        if (isset($user['message'])) {
            $this->dispatchBrowserEvent('show-change-password-modal');
            throw ValidationException::withMessages([
                'current_password' => $user['message'],
            ]);
        }

        $response = app(UserService::class)->resetPasswordWithCurrent($user, $this->current_password, $this->new_password);
        logger($response);
        if (is_array($response) && isset($response['error'])) {
            $this->dispatchBrowserEvent('show-change-password-modal');
            throw ValidationException::withMessages([
                'current_password' => $response['error'],
            ]);
        } elseif (is_array($response) && isset($response['message'])) {
            $this->success = $response['message'];
            $this->dispatch('click');
        } else {
            $this->success = 'Password changed successfully.';
            $this->dispatch('click');
        }
    }

    public function close()
    {
        $this->dispatch('click');
    }

    public function render()
    {
        // $this->current_password = '';
        // $this->new_password = '';
        // $this->confirm_password = '';
        // $this->error = '';
        // $this->success = '';

        return view('livewire.shared.modals.change-password');
    }
}
