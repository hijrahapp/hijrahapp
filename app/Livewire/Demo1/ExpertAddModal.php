<?php

namespace App\Livewire\Demo1;

use App\Http\Controllers\App\UserController;
use App\Models\Role;
use Livewire\Component;

class ExpertAddModal extends Component
{
    public $email = '';
    public $name = '';
    public $password = '';
    public $roleId = '';
    public $roles = [];
    public $error = '';

    protected $rules = [
        'email' => 'required|email|unique:users,email',
        'name' => 'required',
        'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
        'roleId' => 'required|exists:roles,id',
    ];

    protected function messages()
    {
        return [
            'password.min' => __('messages.invalid_password_format'),
            'password.regex' => __('messages.invalid_password_format')
        ];
    }

    protected $listeners = ['reset-modal' => 'resetForm'];

    public function mount()
    {
        $this->roles = Role::whereIn('name', ['Expert'])->orderBy('name', 'asc')->get();
        $this->roleId = $this->roles[0]['id'];
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
        $this->name = '';
        $this->roleId = $this->roles[0]['id'];
    }

    public function render()
    {
        return view('livewire.demo1.expert-add-modal');
    }
}
