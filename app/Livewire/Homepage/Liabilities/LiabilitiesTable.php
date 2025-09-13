<?php

namespace App\Livewire\Homepage\Liabilities;

use App\Models\Liability;
use App\Traits\WithoutUrlPagination;
use App\Traits\WithTableReload;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class LiabilitiesTable extends Component
{
    use WithFileUploads, WithoutUrlPagination, WithTableReload;

    public string $search = '';

    public int $perPage = 10;

    protected $listeners = [
        'refreshTable' => 'reloadTable',
        'deleteLiability' => 'deleteLiability',
        'changeLiabilityStatus' => 'changeLiabilityStatus',
    ];

    #[Computed]
    public function liabilities()
    {
        return $this->handleReloadState(function () {
            $query = Liability::where('name', 'like', '%'.$this->search.'%')
                ->orderBy('created_at', 'desc');

            // Use custom pagination without URL caching
            $page = $this->getPage();

            return $query->paginate($this->perPage, ['*'], 'page', $page);
        });
    }

    public function manageLiability($liabilityId)
    {
        return redirect()->route('liability.manage', ['liabilityId' => $liabilityId]);
    }

    public function viewUsers($liabilityId)
    {
        return redirect()->route('liability.users', ['liability' => $liabilityId]);
    }

    public function openLiabilityStatusModal($request)
    {
        $liability = Liability::findOrFail($request['id']);

        if ($request['active']) {
            $title = __('messages.activate_liability_title');
            $message = __('messages.activate_liability_message');
            $action = __('messages.activate_action');
            $note = null;
        } else {
            $title = __('messages.deactivate_liability_title');
            $message = __('messages.deactivate_liability_message');
            $action = __('messages.deactivate_action');
            $note = __('messages.deactivate_liability_note');
        }

        $modal = [
            'title' => $title,
            'message' => $message,
            'note' => $note,
            'action' => $action,
            'callback' => 'changeLiabilityStatus',
            'object' => $request,
        ];
        $this->dispatch('openConfirmationModal', $modal);
    }

    public function changeLiabilityStatus($request)
    {
        $liability = Liability::findOrFail($request['id']);

        $liability->active = $request['active'];
        $liability->save();
        $this->reloadTable();
        $this->dispatch('show-toast', type: 'success', message: $request['active'] ? 'Liability activated successfully!' : 'Liability deactivated successfully!');
    }

    public function openDeleteLiabilityModal($request)
    {
        $modal = [
            'title' => __('messages.delete_liability_title'),
            'message' => __('messages.delete_liability_message'),
            'note' => __('messages.delete_liability_note'),
            'action' => __('messages.delete_liability_action'),
            'callback' => 'deleteLiability',
            'object' => $request,
        ];

        $this->dispatch('openConfirmationModal', $modal);
    }

    public function deleteLiability($liabilityId)
    {
        try {
            $liability = Liability::find($liabilityId);
            if ($liability) {
                $liability->delete();
                $this->dispatch('show-toast', type: 'success', message: 'Liability deleted successfully.');
                $this->reloadTable();
            }
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Failed to delete liability: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.homepage.liabilities.liabilities-table');
    }
}
