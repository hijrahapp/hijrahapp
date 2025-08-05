<?php

namespace App\Livewire\Homepage\Shared;

use Livewire\Component;

class TopbarUserDropdown extends Component
{
    public $profilePhoto = '';
    public $userName = '';
    public $userEmail = '';
    public $userRole = '';

    public function logout()
    {
        session()->forget('jwt_token');
        return redirect()->route('login');
    }

    public function mount() {
        $user = session('user', []);
        $this->profilePhoto = $user['profilePhoto'] ?? asset('/assets/media/avatars/blank.png');
        $this->userName = $user['name'] ?? 'Guest';
        $this->userEmail = $user['email'] ?? '';
        $this->userRole = $user['role'] ?? '';
    }

    public function render()
    {
        return view('livewire.homepage.shared.topbar-user-dropdown');
    }
}
