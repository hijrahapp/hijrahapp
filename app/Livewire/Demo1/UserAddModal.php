<?php

namespace App\Livewire\Demo1;

use App\Http\Controllers\App\UserController;
use Livewire\Component;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserAddModal extends Component
{
    public $email = '';
    public $name = '';
    public $password = '';
    public $gender = '';
    public $birthDate = '';
    public $roleId = '';
    public $roles = [];
    public $error = '';

    protected $rules = [
        'email' => 'required|email|unique:users,email',
        'name' => 'required',
        'password' => 'required|min:6',
        'gender' => 'required|in:male,female',
        'birthDate' => 'required|date',
        'roleId' => 'required|exists:roles,id',
    ];

    protected $listeners = ['reset-modal' => 'resetForm'];

    public function mount()
    {
        $user = session('user');
        if ($user['role'] == 'SuperAdmin') {
            $this->roles = Role::where('name', '!=', 'SuperAdmin')->orderBy('name', 'asc')->get();
        } else if($user['role'] == 'Admin') {
            $this->roles = Role::whereNotIn('name', ['SuperAdmin', 'Admin'])->orderBy('name', 'asc')->get();
        } else {
            $this->roles = Role::whereNotIn('name', ['SuperAdmin', 'Admin', 'Expert'])->orderBy('name', 'asc')->get();
        }
    }

    public function closeModal()
    {
        $this->dispatch('click');
    }

    public function saveUser()
    {
        $data = $this->validate();
        $response = app(UserController::class)->createNewUser($data);
        if (isset($response['error'])) {
            $this->error = $response['error'];
            return;
        }
        $this->dispatch('refreshUserTable');
        $this->closeModal();
    }

    public function resetForm()
    {
        $this->resetErrorBag();
        $this->email = '';
        $this->password = '';
        $this->gender = '';
        $this->birthDate = '';
        $this->roleId = '';
    }

    public function render()
    {
        return view('livewire.demo1.user-add-modal');
    }
}
