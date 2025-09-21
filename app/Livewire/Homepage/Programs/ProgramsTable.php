<?php

namespace App\Livewire\Homepage\Programs;

use App\Models\Program;
use App\Traits\HasDataTable;
use App\Traits\WithoutUrlPagination;
use App\Traits\WithTableReload;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProgramsTable extends Component
{
    use WithFileUploads, WithoutUrlPagination, WithTableReload, HasDataTable;

    public string $search = '';
    public int $perPage = 10;

    protected string $modelClass = Program::class;
    protected string $entityName = 'program';
    protected array $searchFields = ['name', 'description'];
    protected string $defaultOrderBy = 'created_at';
    protected string $defaultOrderDirection = 'desc';

    public ?string $statusFilter = null;

    protected $listeners = [
        'refreshTable' => 'reloadTable',
        'deleteProgram' => 'deleteProgram',
        'changeProgramStatus' => 'changeProgramStatus',
        'deleteRecord' => 'deleteProgram',
        'changeStatus' => 'changeProgramStatus',
    ];

    #[Computed]
    public function programs()
    {
        return $this->handleReloadState(function () {
            $query = $this->getBaseQuery()
                ->withCount(['stepsList']);

            // Apply status filter if set
            if ($this->statusFilter !== null && $this->statusFilter !== '') {
                $query->where('active', $this->statusFilter === 'active');
            }

            $page = $this->getPage();
            return $query->paginate($this->perPage, ['*'], 'page', $page);
        });
    }

    public function manageProgram($programId)
    {
        return redirect()->route('program.manage', ['programId' => $programId]);
    }

    public function viewUsers($programId)
    {
        return redirect()->route('program.users', ['program' => $programId]);
    }

    public function clearStatusFilter()
    {
        $this->statusFilter = null;
    }

    public function openProgramStatusModal($request)
    {
        $this->openStatusModal($request);
    }

    public function changeProgramStatus($request)
    {
        $this->changeStatus($request);
    }

    public function openDeleteProgramModal($request)
    {
        $this->openDeleteModal($request);
    }

    public function deleteProgram($programId)
    {
        $this->deleteRecord($programId);
    }

    public function render()
    {
        return view('livewire.homepage.programs.programs-table');
    }
}
