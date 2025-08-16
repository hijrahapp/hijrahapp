<?php

namespace App\Livewire\Homepage\Shared;

use Livewire\Component;

class Sidebar extends Component
{
    public function setActiveSection($routeName)
    {
        return redirect()->route($routeName);
    }

    public function render()
    {
        return view('livewire.homepage.shared.sidebar');
    }
}
