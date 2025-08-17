<?php

namespace App\Livewire\Homepage\Methodologies;

use App\Models\Methodology;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class MethodologyManage extends Component
{
    public ?int $methodologyId = null;

    public string $name = '';
    public string $description = '';
    public string $definition = '';
    public string $objectives = '';
    public array $tags = [];
    public string $imgUrl = '';
    public string $type = '';

    public string $error = '';

    // Extra details (by type)
    public string $modulesDefinition = '';
    public string $pillarsDefinition = '';
    public string $numberOfPillars = '';
    public string $firstSectionName = '';
    public string $secondSectionName = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3',
            'description' => 'required|string|min:3',
            'definition' => 'required|string|min:3',
            'objectives' => 'nullable|string',
            'tags' => 'array',
            'tags.*' => 'integer',
            'imgUrl' => 'nullable|string',
            'type' => 'required|in:simple,complex,twoSection',
        ];
    }

    public function mount(int $methodologyId)
    {
        if (!session('jwt_token')) {
            return redirect()->route('login');
        }

        $this->methodologyId = $methodologyId;

        $methodology = Methodology::findOrFail($methodologyId);
        $this->name = $methodology->name;
        $this->description = $methodology->description;
        $this->definition = $methodology->definition;
        $this->objectives = $methodology->objectives ?? '';
        $this->tags = $methodology->tags ?? [];
        $this->imgUrl = $methodology->img_url ?? '';
        $this->type = $methodology->type;

        // Prefill extra details by type
        $this->modulesDefinition = $methodology->modules_definition ?? '';
        $this->pillarsDefinition = $methodology->pillars_definition ?? '';
        $this->numberOfPillars = (string)($methodology->number_of_pillars ?? '');
        $this->firstSectionName = $methodology->first_section_name ?? '';
        $this->secondSectionName = $methodology->second_section_name ?? '';
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
                'tags' => $this->tags,
                'img_url' => $this->imgUrl ?: null,
                'type' => $this->type, // not editable, preserved
            ];

            $methodology = Methodology::findOrFail($this->methodologyId);

            $updateData = $data;
            if ($this->imgUrl === '') {
                unset($updateData['img_url']);
            }

            $methodology->update($updateData);

            $this->dispatch('show-toast', type: 'success', message: 'Methodology details saved successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = $e->validator->errors()->first() ?: 'Please check the form for errors.';
            $this->dispatch('show-toast', type: 'error', message: $firstError);
            throw $e;
        } catch (\Throwable $e) {
            $this->error = 'An unexpected error occurred. Please try again.';
            $this->dispatch('show-toast', type: 'error', message: $this->error);
        }
    }

    public function saveExtraDetails(): void
    {
        $this->resetErrorBag();

        try {
            $methodology = Methodology::findOrFail($this->methodologyId);

            if ($this->type === 'simple') {
                $this->validate([
                    'modulesDefinition' => 'nullable|string',
                ]);

                $methodology->update([
                    'modules_definition' => $this->modulesDefinition,
                ]);
            } elseif ($this->type === 'complex') {
                $this->validate([
                    'pillarsDefinition' => 'nullable|string',
                    'numberOfPillars' => 'nullable|string',
                ]);

                $methodology->update([
                    'pillars_definition' => $this->pillarsDefinition,
                    'number_of_pillars' => is_numeric($this->numberOfPillars)
                        ? (int)$this->numberOfPillars
                        : null,
                ]);
            }

            $this->dispatch('show-toast', type: 'success', message: 'Extra details saved.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = $e->validator->errors()->first() ?: 'Please check the form for errors.';
            $this->dispatch('show-toast', type: 'error', message: $firstError);
            throw $e;
        } catch (\Throwable $e) {
            $this->error = 'An unexpected error occurred. Please try again.';
            $this->dispatch('show-toast', type: 'error', message: $this->error);
        }
    }

    public function savePillars(): void
    {
        $this->dispatch('show-toast', type: 'success', message: 'Pillars saved.');
    }

    public function saveSection1Details(): void
    {
        $this->resetErrorBag();

        try {
            if ($this->type !== 'twoSection') {
                return;
            }

            $this->validate([
                'firstSectionName' => 'required|string|min:3',
            ]);

            $methodology = Methodology::findOrFail($this->methodologyId);
            $methodology->update([
                'first_section_name' => $this->firstSectionName,
            ]);

            $this->dispatch('show-toast', type: 'success', message: 'Section 1 details saved.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = $e->validator->errors()->first() ?: 'Please check the form for errors.';
            $this->dispatch('show-toast', type: 'error', message: $firstError);
            throw $e;
        } catch (\Throwable $e) {
            $this->error = 'An unexpected error occurred. Please try again.';
            $this->dispatch('show-toast', type: 'error', message: $this->error);
        }
    }

    public function saveSection2Details(): void
    {
        $this->resetErrorBag();

        try {
            if ($this->type !== 'twoSection') {
                return;
            }

            $this->validate([
                'secondSectionName' => 'required|string|min:3',
            ]);

            $methodology = Methodology::findOrFail($this->methodologyId);
            $methodology->update([
                'second_section_name' => $this->secondSectionName,
            ]);

            $this->dispatch('show-toast', type: 'success', message: 'Section 2 details saved.');
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
        return redirect()->route('methodologies');
    }

    public function render()
    {
        return view('livewire.homepage.methodologies.methodology-manage');
    }
}


