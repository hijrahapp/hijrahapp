<?php

namespace App\Livewire\Shared\Modals;

use App\Http\Controllers\App\UserDetailsController;
use Livewire\Component;

class UserProfile extends Component
{
    public $email;
    public $name;
    public $gender;
    public $birthdate;

    public function save()
    {
        $this->validate([
            'name' => 'required',
            'gender' => 'required',
            'birthdate' => 'required',
        ]);

        $jwt = session('jwt_token');

        $data = [
            'name' => $this->name,
            'gender' => $this->gender,
            'birthDate' => $this->birthdate
        ];

        $response = app(UserDetailsController::class)->updateUserDetails($jwt, $data);
        if (isset($response['message'])) {
            session()->flash('error', $response['message']);
        }
        session(['user' => $response ?? null]);

        $this->dispatch('click');
        return redirect()->route('demo1.index');
    }

    public function close()
    {
        $this->dispatch('click');
    }

    public function render()
    {
        $user = session('user');
        $this->email = $user['email'] ?? '';
        $this->name = $user['name'] ?? '';
        $this->gender = $user['gender'] ?? '';
        $this->birthdate = $user['birthDate'] ?? '';
        
        return view('livewire.shared.modals.user-profile');
    }
}
