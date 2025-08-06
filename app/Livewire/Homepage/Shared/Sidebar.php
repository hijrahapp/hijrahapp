<?php

namespace App\Livewire\Homepage\Shared;

use Livewire\Component;

class Sidebar extends Component
{
    public $currentSection = 'Home';
    public $currentSubSection = '';
    
    public function mount()
    {
        // Set default section based on user role
        $role = session('user')["role"] ?? null;
        if ($role === 'Expert') {
            $this->currentSection = 'Methodologies';
            $this->currentSubSection = 'Methodologies Management';
        } elseif ($role === 'SuperAdmin' || $role === 'Admin') {
            $this->currentSection = 'Users Management';
            $this->currentSubSection = 'System Users';
        }
    }

    public function setActiveSection($section, $subSection = '')
    {
        $this->currentSection = $section;
        $this->currentSubSection = $subSection;
        
        // Emit event to update the mega-menu header
        $this->dispatch('updateHeader', $section, $subSection);
        
        // For debugging - you can remove this in production
        logger()->info("Header updated: {$section} > {$subSection}");
    }

    public function render()
    {
        return view('livewire.homepage.shared.sidebar');
    }
}
