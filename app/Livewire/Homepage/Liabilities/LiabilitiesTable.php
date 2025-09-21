<?php

namespace App\Livewire\Homepage\Liabilities;

use App\Models\Liability;
use App\Traits\HasDataTable;
use App\Traits\WithoutUrlPagination;
use App\Traits\WithTableReload;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class LiabilitiesTable extends Component
{
    use WithFileUploads, WithoutUrlPagination, WithTableReload, HasDataTable;

    public string $search = '';
    public int $perPage = 10;

    protected string $modelClass = Liability::class;
    protected string $entityName = 'liability';
    protected array $searchFields = ['name', 'description'];
    protected string $defaultOrderBy = 'created_at';
    protected string $defaultOrderDirection = 'desc';

    protected $listeners = [
        'refreshTable' => 'reloadTable',
        'deleteLiability' => 'deleteLiability',
        'changeLiabilityStatus' => 'changeLiabilityStatus',
        'deleteRecord' => 'deleteLiability',
        'changeStatus' => 'changeLiabilityStatus',
    ];

    #[Computed]
    public function liabilities()
    {
        return $this->handleReloadState(function () {
            $query = $this->getBaseQuery();
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
        $this->openStatusModal($request);
    }

    public function changeLiabilityStatus($request)
    {
        $this->changeStatus($request);
    }

    public function openDeleteLiabilityModal($request)
    {
        $this->openDeleteModal($request);
    }

    public function deleteLiability($liabilityId)
    {
        $this->deleteRecord($liabilityId);
    }

    public function render()
    {
        return view('livewire.homepage.liabilities.liabilities-table');
    }
}
