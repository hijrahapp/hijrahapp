<?php

namespace App\Livewire\Homepage\Modals;

use Livewire\Component;

class ConfirmationModal extends Component
{
    public $title;
    public $message;
    public $action;
    public $callBack;
    public $object;

    protected $listeners = [
        'openConfirmationModal' => 'openModal',
        'reset-modal' => 'resetForm',
    ];

    public function openModal($title, $message, $action, $callBack, $object)
    {
        $this->title = $title;
        $this->message = $message;
        $this->action = $action;
        $this->callBack = $callBack;
        $this->object = $object;
    }

    public function closeModal()
    {
        $this->resetForm();
        $this->dispatch('click');
    }

    public function performConfirmAction()
    {
        $this->dispatch($this->callBack, $this->object);
        $this->closeModal();
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
