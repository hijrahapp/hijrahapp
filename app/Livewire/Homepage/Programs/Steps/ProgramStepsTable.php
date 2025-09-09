<?php

namespace App\Livewire\Homepage\Programs\Steps;

use App\Models\Program;
use App\Models\Step;
use App\Traits\WithTableReload;
use Livewire\Component;
use Livewire\WithPagination;

class ProgramStepsTable extends Component
{
    use WithPagination, WithTableReload;

    public Program $program;

    public string $search = '';

    public int $perPage = 10;

    protected $listeners = [
        'refreshTable' => 'reloadTable',
        'deleteStep' => 'deleteStep',
    ];

    public function mount(Program $program)
    {
        $this->program = $program;
    }

    public function getStepsProperty()
    {
        return $this->handleReloadState(function () {
            return $this->program->stepsList()
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->ordered()
                ->paginate($this->perPage);
        });
    }

    public function openAddModal()
    {
        $this->dispatch('open-add-step-modal', programId: $this->program->id);
    }

    public function openEditModal($stepId)
    {
        $this->dispatch('edit-step', stepId: $stepId);
    }

    public function manageQuestions($stepId)
    {
        $step = Step::find($stepId);

        if ($step && $step->program_id === $this->program->id && $step->type === 'quiz') {
            // TODO: Implement questions management modal similar to methodology questions
            $this->dispatch('showAlert', [
                'type' => 'info',
                'title' => 'Coming Soon',
                'message' => 'Questions management for quiz steps will be available soon.',
            ]);
        } else {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'Error!',
                'message' => 'Step not found or not a quiz type.',
            ]);
        }
    }

    public function deleteStep($stepId)
    {
        try {
            $step = Step::find($stepId);
            if ($step && $step->program_id === $this->program->id) {
                $step->delete();
                $this->dispatch('showAlert', [
                    'type' => 'success',
                    'title' => 'Success!',
                    'message' => 'Step deleted successfully.',
                ]);
                $this->reloadTable();
            }
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'Error!',
                'message' => 'Failed to delete step: '.$e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.homepage.programs.steps.program-steps-table', [
            'steps' => $this->steps,
        ]);
    }
}
