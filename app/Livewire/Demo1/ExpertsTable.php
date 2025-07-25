<?php

namespace App\Livewire\Demo1;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ExpertsTable extends Component
{
    use WithFileUploads, WithPagination;

    public $search = '';
    public $perPage = 15;

    protected $paginationTheme = 'tailwind';

    protected $listeners = [
        'refreshUserTable' => '$refresh'
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function mount()
    {
    }

    public function getUsersProperty()
    {
        $expertsRoleId = Role::where('name', 'Expert')->value('id');
        $query = User::with('role')
            ->whereIn('roleId', [$expertsRoleId])
            ->orderBy('created_at', 'asc')
            ->when($this->search, function($q) {
                $q->where(function($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            });
        return $query->paginate($this->perPage);
    }

    public function handleUserEditOpen($user)
    {
        $this->dispatch('openUserEditModal', $user);
    }

    public function handleUserStatusOpen($request) {
        $this->dispatch('openUserStatusModal', $request['userId'], $request['status']);
    }

    public function render()
    {
        logger("renderrrr");
        return view('livewire.demo1.experts-table', [
            'users' => $this->getUsersProperty(),
        ]);
    }

    public function isUserEditable($user) {
        if ($user->role->name->value === 'SuperAdmin') {
            return false;
        }

        $currentUser = session('user') ?? null;
        if($user->email === $currentUser['email']) {
            return false;
        }

        if ($currentUser['role'] === $user->role->name->value) {
            return false;
        }

        return true;
    }
}
