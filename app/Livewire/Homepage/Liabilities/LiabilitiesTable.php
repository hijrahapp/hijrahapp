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

    public function deleteLiability($liabilityId)
    {
        try {
            $liability = Liability::find($liabilityId);
            if ($liability) {
                $liability->delete();
                session()->flash('success', 'Liability deleted successfully.');
                $this->dispatch('refreshTable');
            } else {
                session()->flash('error', 'Liability not found.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete liability: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.homepage.liabilities.liabilities-table');
    }
}
