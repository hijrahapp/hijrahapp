<?php

namespace App\Livewire\Shared;

use Livewire\Component;

class TopbarUserDropdown extends Component
{
    public function logout()
    {
        session()->forget('jwt_token');
        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.shared.topbar-user-dropdown');
    }
}
