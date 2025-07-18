<?php

namespace App\Livewire\Demo1;

use Livewire\Component;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserEditModal extends Component
{
    public $userId;
    public $email = '';
    public $name = '';
    public $password = '';
    public $gender = '';
    public $birthDate = '';
    public $roleId = '';
    public $allRoles = [];
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
        $this->allRoles = Role::orderBy('name', 'asc')->get();

        if ($user['role'] == 'SuperAdmin') {
            $this->roles = $this->allRoles->filter(function($role) {
                return $role->name !== 'SuperAdmin';
            })->values();
        } else if ($user['role'] == 'Admin') {
            $this->roles = $this->allRoles->filter(function($role) {
                return !in_array($role->name, ['SuperAdmin', 'Admin']);
            })->values();
        } else {
            $this->roles = $this->allRoles->filter(function($role) {
                return !in_array($role->name, ['SuperAdmin', 'Admin', 'Expert']);
            })->values();
        }
    }

    public function openUserEditModal($user)
    {
        //here
        $superAdminRole = $this->allRoles->firstWhere('name', 'SuperAdmin');
        if ($superAdminRole && $this->roleId == $superAdminRole->id) {
            $this->roles = $this->allRoles;
        }

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
        $data = $this->validate();
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
