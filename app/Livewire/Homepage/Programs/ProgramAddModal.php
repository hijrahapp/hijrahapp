<?php

namespace App\Livewire\Homepage\Programs;

use App\Models\Program;
use Livewire\Component;

class ProgramAddModal extends Component
{
    public string $name = '';

    public string $description = '';

    public string $definition = '';

    public string $objectives = '';

    public string $error = '';

    public bool $isEditMode = false;

    public ?int $programId = null;

    protected function rules()
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:3|max:200',
            'definition' => 'required|string|min:3',
            'objectives' => 'nullable|string',
        ];
    }

    protected $listeners = [
        'reset-modal' => 'resetForm',
        'edit-program' => 'editProgram',
    ];

    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->definition = '';
        $this->objectives = '';
        $this->error = '';
        $this->isEditMode = false;
        $this->programId = null;
        $this->resetValidation();
    }

    public function editProgram($programId)
    {
        $program = Program::find($programId);
        if ($program) {
            $this->programId = $program->id;
            $this->name = $program->name;
            $this->description = $program->description;
            $this->definition = $program->definition;
            $this->objectives = $program->objectives;
            $this->isEditMode = true;
        }
    }

    public function saveProgram()
    {
        $this->validate();

        try {
            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'definition' => $this->definition,
                'objectives' => $this->objectives,
            ];

            if ($this->isEditMode && $this->programId) {
                $program = Program::find($this->programId);
                $program->update($data);
                $message = 'Program updated successfully';
            } else {
                Program::create($data);
                $message = 'Program created successfully';
            }

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'Success!',
                'message' => $message,
            ]);

            $this->dispatch('refreshTable');
            $this->resetForm();
            $this->dispatch('closeModal');
        } catch (\Exception $e) {
            $this->error = 'Failed to save program: '.$e->getMessage();
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'Error!',
                'message' => $this->error,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.homepage.programs.program-add-modal');
    }
}
