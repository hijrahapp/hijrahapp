<?php

namespace App\Livewire\Homepage\Users\Experts;

use App\Models\Role;
use App\Models\User;
use App\Traits\WithoutUrlPagination;
use Livewire\Component;
use Livewire\WithFileUploads;

class ExpertsTable extends Component
{
    use WithFileUploads, WithoutUrlPagination;

    public $search = '';
    public $perPage = 10;

    protected $listeners = [
        'refreshTable' => '$refresh',
        'changeUserStatus' => 'changeUserStatus'
    ];

    public function getUsersProperty()
    {
        $expertsRoleId = Role::where('name', 'Expert')->value('id');
        $query = User::with('role')
            ->whereIn('roleId', [$expertsRoleId])
            ->orderBy('id', 'asc')
            ->when($this->search, function($q) {
                $q->where(function($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            });

        // Use custom pagination without URL caching
        $page = $this->getPage();
        return $query->paginate($this->perPage, ['*'], 'page', $page);
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
        return view('livewire.homepage.users.experts.experts-table', [
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
