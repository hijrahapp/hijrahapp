<?php

namespace App\Livewire\Shared\Modals;

use App\Http\Controllers\App\UserDetailsController;
use Livewire\Component;

class UserProfile extends Component
{
    public $email;
    public $name;
//    public $gender;
//    public $birthdate;
    public $error;

    protected $listeners = ['reset-modal' => 'resetForm'];

    public function save()
    {
        $this->error = '';
        $this->validate([
            'name' => 'required',
//            'gender' => 'required',
//            'birthdate' => 'required',
        ]);

        $jwt = session('jwt_token');

        $data = [
            'name' => $this->name,
//            'gender' => $this->gender,
//            'birthDate' => $this->birthdate
        ];

        $response = app(UserDetailsController::class)->updateUserDetails($jwt, $data);
        if (isset($response['message'])) {
            $this->error = $response['message'];
            return;
        }
        session(['user' => $response ?? null]);

        $this->close();
        return redirect()->route('demo1.index');
    }

    public function close()
    {
        $this->dispatch('click');
    }

    public function resetForm()
    {
        $this->resetErrorBag();
        $user = session('user');
        $this->email = $user['email'] ?? '';
        $this->name = $user['name'] ?? '';
//        $this->gender = $user['gender'] ?? '';
//        $this->birthdate = $user['birthDate'] ?? '';
    }

    public function mount()
    {
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.shared.modals.user-profile');
    }
}
