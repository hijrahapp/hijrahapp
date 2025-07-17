<?php

namespace App\Livewire\Shared\Modals;

use Livewire\Component;

class UserProfile extends Component
{
    public $email;
    public $name;
    public $gender;
    public $birthdate;

    public function mount()
    {
        $user = session('user');
        $this->email = $user['email'] ?? '';
        $this->name = $user['name'] ?? '';
        $this->gender = $user['gender'] ?? '';
        $this->birthdate = $user['birthDate'] ?? '';
    }

    public function save()
    {
        // Save logic here
    }

    public function resetPassword()
    {
        // Reset password logic here
    }

    public function render()
    {
        return view('livewire.shared.modals.user-profile');
    }
} 