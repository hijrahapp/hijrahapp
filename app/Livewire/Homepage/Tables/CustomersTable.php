<?php

namespace App\Livewire\Homepage\Tables;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class CustomersTable extends Component
{
    use WithFileUploads, WithPagination;

    public $search = '';
    public $perPage = 15;

    protected $paginationTheme = 'tailwind';

    protected $listeners = [
        'refreshTable' => '$refresh'
    ];

    public function getSearchProperty()
    {
        return $this->search;
    }

    public function setSearchProperty($value)
    {
        $this->search = $value;
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
            ->orderBy('id', 'asc')
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
        return view('livewire.homepage.tables.customers-table', [
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
