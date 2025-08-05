<?php

namespace App\Livewire\Homepage\Shared;

use Livewire\Component;
use Livewire\Attributes\On;

class MegaMenu extends Component
{
    public $currentSection = 'Home';
    public $currentSubSection = '';

    public function mount()
    {
        // Set default section based on user role
        $role = session('user')["role"] ?? null;
        if ($role === 'Expert') {
            $this->currentSection = 'Welcome';
        } elseif ($role === 'SuperAdmin' || $role === 'Admin') {
            $this->currentSection = 'Network';
            $this->currentSubSection = 'Admins Management';
        }
    }

    #[On('updateHeader')]
    public function handleUpdateHeader($section, $subSection = '')
    {
        $this->currentSection = $section;
        $this->currentSubSection = $subSection;

        // For debugging - you can remove this in production
        logger()->info("MegaMenu received updateHeader event: {$section} > {$subSection}");
    }

    public function render()
    {
        return view('livewire.homepage.shared.mega-menu');
    }
}
