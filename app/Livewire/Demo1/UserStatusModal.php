<?php

namespace App\Livewire\Demo1;

use Livewire\Component;
use App\Models\User;

class UserStatusModal extends Component
{
    public $userId;
    public $active;
    public $confirmAction;

    protected $listeners = [
        'openUserStatusModal' => 'openUserStatusModal',
        'reset-modal' => 'resetForm',
    ];

    public function openUserStatusModal($userId, $status)
    {
        $this->userId = $userId;
        $this->active = $status;
        $this->confirmAction = $status ? 'activate' : 'deactivate';
        $this->dispatch('open-modal', id: 'user_status_modal');
    }

    public function closeModal()
    {
        $this->resetForm();
        $this->dispatch('click');
    }

    public function performConfirmAction()
    {
        $user = User::findOrFail($this->userId);
        $user->active = $this->active;
        $user->save();
        $this->closeModal();
        $this->dispatch('refreshUserTable');
    }

    public function resetForm()
    {
        $this->userId = null;
        $this->active = null;
        $this->confirmAction = null;
    }

    public function render()
    {
        return view('livewire.demo1.user-status-modal');
    }
}
