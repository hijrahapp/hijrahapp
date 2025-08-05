<?php

namespace App\Livewire\Homepage\Modals;

use App\Models\User;
use Livewire\Component;

class UserEditModal extends Component
{
    public $userId;
    public $email = '';
    public $name = '';

    protected $listeners = [
        'openUserEditModal' => 'openUserEditModal',
        'reset-modal' => 'resetForm',
    ];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    public function mount()
    {
    }

    public function openUserEditModal($user)
    {
        $this->userId = $user['id'];
        $this->email = $user['email'];
        $this->name = $user['name'];
    }

    public function closeModal()
    {
        $this->dispatch('click');
    }

    public function saveUser()
    {
        $this->validate();
        $user = User::findOrFail($this->userId);
        $user->name = $this->name;
        $user->save();
        $this->closeModal();
        $this->dispatch('refreshUserTable');
    }

    public function resetForm()
    {
        $this->resetErrorBag();
        $this->userId = null;
        $this->email = '';
        $this->name = '';
    }

    public function render()
    {
        return view('livewire.homepage.modals.user-edit-modal');
    }
}
