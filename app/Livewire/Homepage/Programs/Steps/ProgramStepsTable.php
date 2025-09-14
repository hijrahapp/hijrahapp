<?php

namespace App\Livewire\Homepage\Programs\Steps;

use App\Models\Program;
use App\Models\Step;
use App\Traits\WithoutUrlPagination;
use App\Traits\WithTableReload;
use Livewire\Component;

class ProgramStepsTable extends Component
{
    use WithoutUrlPagination, WithTableReload;

    public Program $program;

    public string $search = '';

    public ?string $typeFilter = null;

    public int $perPage = 10;

    protected $listeners = [
        'refreshTable' => 'reloadTable',
        'removeStep' => 'removeStep',
    ];

    public function mount(Program $program)
    {
        $this->program = $program;
    }

    public function getStepsProperty()
    {
        return $this->handleReloadState(function () {
            $query = $this->program->stepsList()
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->when($this->typeFilter !== null && $this->typeFilter !== '', function ($query) {
                    $query->where('type', $this->typeFilter);
                })
                ->ordered();

            // Use custom pagination without URL caching
            $page = $this->getPage();

            return $query->paginate($this->perPage, ['*'], 'page', $page);
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
        $this->dispatch('open-step-questions-modal', stepId: $stepId);
    }

    public function openRemoveStepModal($request)
    {
        $modal = [
            'title' => __('messages.remove_title'),
            'message' => __('messages.remove_message'),
            'note' => __('messages.remove_note'),
            'action' => __('messages.remove_action'),
            'callback' => 'removeStep',
            'object' => $request,
        ];

        $this->dispatch('openConfirmationModal', $modal);
    }

    public function removeStep($stepId)
    {
        try {
            $step = Step::find($stepId);
            if ($step && $step->program_id === $this->program->id) {
                $step->delete();
                $this->dispatch('show-toast', type: 'success', message: 'Step removed successfully.');
                $this->reloadTable();
            }
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Failed to remove step: '.$e->getMessage());
        }
    }

    public function getStepTypesProperty()
    {
        return Step::TYPES;
    }

    public function render()
    {
        return view('livewire.homepage.programs.steps.program-steps-table', [
            'steps' => $this->steps,
        ]);
    }
}
