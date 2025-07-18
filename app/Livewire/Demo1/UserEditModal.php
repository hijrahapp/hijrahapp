<?php

namespace App\Livewire\Demo1;

use Livewire\Component;
use App\Models\Role;
use App\Models\User;

class UserEditModal extends Component
{
    public $userId;
    public $email = '';
    public $name = '';
    public $password = '';
    public $gender = '';
    public $birthDate = '';
    public $roleId = '';
    public $roles = [];

    protected $listeners = [
        'openUserEditModal' => 'openUserEditModal',
        'reset-modal' => 'resetForm',
    ];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'birthDate' => 'required|date',
            'roleId' => 'required|exists:roles,id',
        ];
    }

    public function mount()
    {
        $user = session('user');
        if ($user['role'] == 'SuperAdmin') {
            $this->roles = Role::where('name', '!=', 'SuperAdmin')->orderBy('name', 'asc')->get();
        } elseif($user['role'] == 'Admin') {
            $this->roles = Role::whereNotIn('name', ['SuperAdmin', 'Admin'])->orderBy('name', 'asc')->get();
        } else {
            $this->roles = Role::whereNotIn('name', ['SuperAdmin', 'Admin', 'Expert'])->orderBy('name', 'asc')->get();
        }
    }

    public function openUserEditModal($user)
    {
        $this->userId = $user['id'];
        $this->email = $user['email'];
        $this->name = $user['name'];
        $this->gender = $user['gender'];
        $this->birthDate = $user['birthDate'];
        $this->roleId = $user['roleId'];
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
        $user->gender = $this->gender;
        $user->birthDate = $this->birthDate;
        $user->roleId = $this->roleId;
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
        $this->password = '';
        $this->gender = '';
        $this->birthDate = '';
        $this->roleId = '';
    }

    public function render()
    {
        return view('livewire.demo1.user-edit-modal');
    }
}
