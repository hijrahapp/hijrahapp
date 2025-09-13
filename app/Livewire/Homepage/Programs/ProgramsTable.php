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

    public ?string $statusFilter = null;

    public int $perPage = 10;

    protected $listeners = [
        'refreshTable' => 'reloadTable',
        'deleteProgram' => 'deleteProgram',
        'changeProgramStatus' => 'changeProgramStatus',
    ];

    #[Computed]
    public function programs()
    {
        return $this->handleReloadState(function () {
            $query = Program::where('name', 'like', '%'.$this->search.'%')
                ->withCount(['stepsList'])
                ->orderBy('created_at', 'desc');

            // Apply status filter if set
            if ($this->statusFilter !== null && $this->statusFilter !== '') {
                $query->where('active', $this->statusFilter === 'active');
            }

            // Use custom pagination without URL caching
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
        $program = Program::findOrFail($request['id']);

        if ($request['active']) {
            $title = __('messages.activate_program_title');
            $message = __('messages.activate_program_message');
            $action = __('messages.activate_action');
            $note = null;
        } else {
            $title = __('messages.deactivate_program_title');
            $message = __('messages.deactivate_program_message');
            $action = __('messages.deactivate_action');
            $note = __('messages.deactivate_program_note');
        }

        $modal = [
            'title' => $title,
            'message' => $message,
            'note' => $note,
            'action' => $action,
            'callback' => 'changeProgramStatus',
            'object' => $request,
        ];
        $this->dispatch('openConfirmationModal', $modal);
    }

    public function changeProgramStatus($request)
    {
        $program = Program::findOrFail($request['id']);

        $program->active = $request['active'];
        $program->save();
        $this->reloadTable();
        $this->dispatch('show-toast', type: 'success', message: $request['active'] ? 'Program activated successfully!' : 'Program deactivated successfully!');
    }

    public function openDeleteProgramModal($request)
    {
        $modal = [
            'title' => __('messages.delete_program_title'),
            'message' => __('messages.delete_program_message'),
            'note' => __('messages.delete_program_note'),
            'action' => __('messages.delete_action'),
            'callback' => 'deleteProgram',
            'object' => $request,
        ];

        $this->dispatch('openConfirmationModal', $modal);
    }

    public function deleteProgram($programId)
    {
        try {
            $program = Program::find($programId);
            if ($program) {
                $program->delete();
                $this->dispatch('show-toast', type: 'success', message: 'Program deleted successfully.');
                $this->reloadTable();
            }
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Failed to delete program: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.homepage.programs.programs-table');
    }
}
