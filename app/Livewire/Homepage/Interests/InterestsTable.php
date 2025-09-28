<?php

namespace App\Livewire\Homepage\Interests;

use App\Models\Interest;
use App\Traits\WithoutUrlPagination;
use App\Traits\WithTableReload;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class InterestsTable extends Component
{
    use WithFileUploads, WithoutUrlPagination, WithTableReload;

    public string $search = '';

    public int $perPage = 10;

    protected $listeners = [
        'refreshTable' => 'reloadTable',
        'deleteInterest' => 'deleteInterest',
        'changeInterestStatus' => 'changeInterestStatus',
    ];

    #[Computed]
    public function interests()
    {
        return $this->handleReloadState(function () {
            $query = Interest::where('name', 'like', '%'.$this->search.'%')
                ->orderBy('created_at', 'desc');

            // Use custom pagination without URL caching
            $page = $this->getPage();

            return $query->paginate($this->perPage, ['*'], 'page', $page);
        });
    }

    public function openInterestStatusModal($request)
    {
        $interest = Interest::findOrFail($request['id']);

        if ($request['active']) {
            $title = 'Activate Interest';
            $message = 'Are you sure you want to activate this interest?';
            $action = 'Activate';
            $note = null;
        } else {
            $title = 'Deactivate Interest';
            $message = 'Are you sure you want to deactivate this interest?';
            $action = 'Deactivate';
            $note = 'This interest will not be available for users.';
        }

        $modal = [
            'title' => $title,
            'message' => $message,
            'note' => $note,
            'action' => $action,
            'callback' => 'changeInterestStatus',
            'object' => $request,
        ];
        $this->dispatch('openConfirmationModal', $modal);
    }

    public function changeInterestStatus($request)
    {
        $interest = Interest::findOrFail($request['id']);

        $interest->active = $request['active'];
        $interest->save();
        $this->reloadTable();
        $this->dispatch('show-toast', type: 'success', message: $request['active'] ? 'Interest activated successfully!' : 'Interest deactivated successfully!');
    }

    public function openDeleteInterestModal($request)
    {
        $modal = [
            'title' => 'Delete Interest',
            'message' => 'Are you sure you want to delete this interest?',
            'note' => 'This action cannot be undone.',
            'action' => 'Delete',
            'callback' => 'deleteInterest',
            'object' => $request,
        ];

        $this->dispatch('openConfirmationModal', $modal);
    }

    public function deleteInterest($interestId)
    {
        try {
            $interest = Interest::find($interestId);
            if ($interest) {
                $interest->delete();
                $this->dispatch('show-toast', type: 'success', message: 'Interest deleted successfully.');
                $this->reloadTable();
            }
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Failed to delete interest: '.$e->getMessage());
        }
    }

    public function editInterest($interestId)
    {
        $this->dispatch('edit-interest', $interestId);
    }

    public function render()
    {
        return view('livewire.homepage.interests.interests-table');
    }
}
