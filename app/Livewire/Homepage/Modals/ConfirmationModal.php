<?php

namespace App\Livewire\Homepage\Modals;

use Livewire\Component;

class ConfirmationModal extends Component
{
    public $title;
    public $message;
    public $note;
    public $action;
    public $callBack;
    public $object;

    protected $listeners = [
        'openConfirmationModal' => 'openModal',
        'reset-modal' => 'resetForm',
    ];

    public function openModal($modal)
    {
        $this->title = $modal['title'] ?? null;
        $this->message = $modal['message'] ?? null;
        $this->note = $modal['note'] ?? null;
        $this->action = $modal['action'] ?? null;
        $this->callBack = $modal['callback'] ?? null;
        $this->object = $modal['object'] ?? null;

        $this->dispatch('show-modal', selector: '#confirmation_modal');
    }
    
    public function performConfirmAction()
    {
        $this->dispatch($this->callBack, $this->object);
        $this->dispatch('click');
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->title = '';
        $this->message = '';
        $this->action = '';
        $this->callBack = '';
        $this->object = null;
    }

    public function render()
    {
        return view('livewire.homepage.modals.confirmation-modal');
    }
}
