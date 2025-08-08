<?php

namespace App\Livewire\Homepage\Tables;

use App\Models\Role;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class AdminsTable extends Component
{
    use WithFileUploads, WithPagination;

    public $search = '';
    public $perPage = 10;

    protected $paginationTheme = 'tailwind';

    protected $listeners = [
        'refreshTable' => '$refresh',
        'changeUserStatus' => 'changeUserStatus'
    ];

    #[Computed]
    public function users()
    {
        $user = session('user');
        if ($user['role'] === 'SuperAdmin')
        {
            $superAdmin = Role::where('name', 'SuperAdmin')->value('id');
            $adminRoleId = Role::where('name', 'Admin')->value('id');
            $query = User::with('role')
                ->whereIn('roleId', [$superAdmin, $adminRoleId])
                ->orderBy('id', 'asc')
                ->when($this->search, function($q) {
                    $q->where(function($q) {
                        $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                    });
                });
        } else {
            $adminRoleId = Role::where('name', 'Admin')->value('id');
            $query = User::with('role')
                ->whereIn('roleId', [$adminRoleId])
                ->orderBy('id', 'asc')
                ->when($this->search, function($q) {
                    $q->where(function($q) {
                        $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                    });
                });
        }
        return $query->paginate($this->perPage);
    }

    public function handleUserEditOpen($user)
    {
        $this->dispatch('openUserEditModal', $user);
    }

    public function handleUserStatusOpen($request) {
        if($request['status']) {
            $title = __('messages.activate_user_title');
            $message = __('messages.activate_user_message');
            $action = __('messages.activate_action');
            $note = null;
        } else {
            $title = __('messages.deactivate_user_title');
            $message = __('messages.deactivate_user_message');
            $action = __('messages.deactivate_action');
            $note = __('messages.deactivate_user_note');
        }
        $modal = [
            'title' => $title,
            'message' => $message,
            'note' => $note,
            'action' => $action,
            'callback' => 'changeUserStatus',
            'object' => $request
        ];
        $this->dispatch('openConfirmationModal', $modal);
    }

    public function changeUserStatus($request)
    {
        $user = User::findOrFail($request['userId']);
        $user->active = $request['status'];
        $user->save();
        $this->dispatch('refreshTable');
    }

    public function render()
    {
        return view('livewire.homepage.tables.admins-table', [
            'users' => $this->users,
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
