<?php

namespace App\Livewire\Homepage\Programs;

use App\Models\Program;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class ProgramManage extends Component
{
    public ?int $programId = null;

    // Program basic details
    public string $name = '';

    public string $description = '';

    public string $definition = '';

    public string $objectives = '';

    public string $img_url = '';

    // Dirty state flags per section
    public bool $isBasicDirty = false;

    public bool $isModulesDirty = false;

    public bool $isStepsDirty = false;

    public string $error = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3',
            'description' => 'required|string|min:3|max:500',
            'definition' => 'required|string|min:3',
            'objectives' => 'nullable|string',
            'img_url' => 'nullable|string',
        ];
    }

    public function mount(int $programId)
    {
        if (! session('jwt_token')) {
            return redirect()->route('login');
        }

        $this->programId = $programId;

        $program = Program::findOrFail($programId);
        $this->name = $program->name;
        $this->description = $program->description;
        $this->definition = $program->definition;
        $this->objectives = $program->objectives ?? '';
        $this->img_url = $program->img_url ?? '';
    }

    public function saveBasicDetails()
    {
        $this->resetErrorBag();

        try {
            $this->validate();

            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'definition' => $this->definition,
                'objectives' => $this->objectives,
                'img_url' => $this->img_url,
            ];

            $program = Program::findOrFail($this->programId);
            $program->update($data);

            $this->isBasicDirty = false;
            $this->dispatch('section-saved', section: 'basic');
            $this->dispatch('show-toast', type: 'success', message: 'Program details saved successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = $e->validator->errors()->first() ?: 'Please check the form for errors.';
            $this->dispatch('show-toast', type: 'error', message: $firstError);
            throw $e;
        } catch (\Throwable $e) {
            $this->error = 'An unexpected error occurred. Please try again.';
            $this->dispatch('show-toast', type: 'error', message: $this->error);
        }
    }

    public function backToList()
    {
        return redirect()->route('programs');
    }

    public function render()
    {
        $program = Program::findOrFail($this->programId);

        return view('livewire.homepage.programs.program-manage', [
            'program' => $program,
        ]);
    }

    public function updated(string $property, mixed $value): void
    {
        // Basic information fields
        if (in_array($property, ['name', 'description', 'definition', 'steps', 'img_url'], true)) {
            $this->isBasicDirty = true;

            return;
        }
    }
}
