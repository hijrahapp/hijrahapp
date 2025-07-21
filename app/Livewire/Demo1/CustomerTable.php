<?php

namespace App\Livewire\Demo1;

use Livewire\Component;
use App\Models\User;
use Livewire\WithFileUploads;
use App\Models\Role;
use Livewire\WithPagination;

class CustomerTable extends Component
{
    use WithFileUploads, WithPagination;

    public $search = '';
    public $perPage = 10;

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
        $customerRoleId = Role::where('name', 'Customer')->value('id');
        $query = User::with('role')
            ->whereIn('roleId', [$customerRoleId])
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
        return view('livewire.demo1.customer-table', [
            'users' => $this->getUsersProperty(),
        ]);
    }

    public function isUserEditable($user) {
        $currentUser = session('user') ?? null;
        if ($currentUser['role'] === "Expert") {
            return false;
        }

        return true;
    }
}
