<?php

namespace App\Livewire\Homepage\Programs;

use App\Models\Program;
use App\Traits\WithoutUrlPagination;
use App\Traits\WithTableReload;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProgramsTable extends Component
{
    use WithFileUploads, WithoutUrlPagination, WithTableReload;

    public string $search = '';

    public int $perPage = 10;

    protected $listeners = [
        'refreshTable' => 'reloadTable',
        'deleteProgram' => 'deleteProgram',
    ];

    #[Computed]
    public function programs()
    {
        return $this->handleReloadState(function () {
            $query = Program::where('name', 'like', '%'.$this->search.'%')
                ->withCount(['stepsList'])
                ->orderBy('created_at', 'desc');

            // Use custom pagination without URL caching
            $page = $this->getPage();

            return $query->paginate($this->perPage, ['*'], 'page', $page);
        });
    }

    public function manageProgram($programId)
    {
        return redirect()->route('program.manage', ['programId' => $programId]);
    }

    public function deleteProgram($programId)
    {
        try {
            $program = Program::find($programId);
            if ($program) {
                $program->delete();
                $this->dispatch('showAlert', [
                    'type' => 'success',
                    'title' => 'Success!',
                    'message' => 'Program deleted successfully.',
                ]);
                $this->reloadTable();
            }
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'Error!',
                'message' => 'Failed to delete program: '.$e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.homepage.programs.programs-table');
    }
}
