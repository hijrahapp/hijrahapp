<?php

namespace App\Livewire\Homepage\Users\Customers;

use App\Models\Role;
use App\Models\User;
use App\Traits\HasDataTable;
use App\Traits\WithoutUrlPagination;
use App\Traits\WithTableReload;
use Livewire\Component;
use Livewire\WithFileUploads;

class CustomersTable extends Component
{
    use WithFileUploads, WithoutUrlPagination, WithTableReload, HasDataTable;

    public $search = '';
    public $perPage = 10;

    protected string $modelClass = User::class;
    protected string $entityName = 'user';
    protected array $searchFields = ['name', 'email'];
    protected string $defaultOrderBy = 'created_at';
    protected string $defaultOrderDirection = 'desc';

    protected $listeners = [
        'refreshTable' => 'reloadTable',
        'changeUserStatus' => 'changeUserStatus',
        'changeStatus' => 'changeUserStatus',
    ];

    public function getUsersProperty()
    {
        return $this->handleReloadState(function () {
            $customerRoleId = Role::where('name', 'Customer')->value('id');
            $query = $this->getBaseQuery()
                ->with('role')
                ->whereIn('roleId', [$customerRoleId])
                ->orderBy('id', 'asc');

            $page = $this->getPage();
            return $query->paginate($this->perPage, ['*'], 'page', $page);
        });
    }

    public function handleUserEditOpen($user)
    {
        $this->dispatch('openUserEditModal', $user);
    }

    public function handleUserStatusOpen($request) {
        $statusRequest = ['id' => $request['userId'], 'active' => $request['status']];
        $this->openStatusModal($statusRequest);
    }

    public function changeUserStatus($request)
    {
        if (isset($request['userId'])) {
            $statusRequest = ['id' => $request['userId'], 'active' => $request['status']];
        } else {
            $statusRequest = $request;
        }
        $this->changeStatus($statusRequest);
    }

    public function render()
    {
        return view('livewire.homepage.users.customers.customers-table', [
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
