<?php

namespace App\Livewire\Homepage\Shared;

use Illuminate\Support\Facades\Route;
use Livewire\Attributes\On;
use Livewire\Component;

class MegaMenu extends Component
{
    public $currentSection;

    public $currentSubSection;

    public function mount()
    {
        $routeName = Route::currentRouteName();

        switch ($routeName) {
            case 'admins':
                $this->currentSection = 'Network';
                $this->currentSubSection = 'Admins Management';
                break;
            case 'customers':
                $this->currentSection = 'Network';
                $this->currentSubSection = 'Customers Management';
                break;
            case 'experts':
                $this->currentSection = 'Network';
                $this->currentSubSection = 'Experts Management';
                break;
            case 'methodologies':
            case 'methodology.manage':
            case 'methodology.questions':
            case 'methodology.users':
            case 'methodology.user.answers':
                $this->currentSection = 'Methodologies';
                $this->currentSubSection = 'Methodologies Management';
                break;
            case 'pillars':
                $this->currentSection = 'Methodologies';
                $this->currentSubSection = 'Pillars Management';
                break;
            case 'modules':
                $this->currentSection = 'Methodologies';
                $this->currentSubSection = 'Modules Management';
                break;
            case 'programs':
            case 'program.manage':
            case 'program.users':
            case 'program.user.answers':
                $this->currentSection = 'Programs';
                $this->currentSubSection = 'Programs Management';
                break;
            case 'liabilities':
            case 'liability.manage':
            case 'liability.users':
            case 'liability.user.details':
                $this->currentSection = 'Liabilities';
                $this->currentSubSection = 'Liabilities Management';
                break;
            case 'questions':
                $this->currentSection = 'Methodologies';
                $this->currentSubSection = 'Questions Management';
                break;
            case 'tags':
                $this->currentSection = 'Methodologies';
                $this->currentSubSection = 'Tags Management';
                break;
            default:
                $this->currentSection = 'Home';
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
