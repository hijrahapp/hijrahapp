<?php

namespace App\Livewire\Homepage\Methodologies;

use App\Models\Methodology;
use Livewire\Component;

class MethodologyAddModal extends Component
{
    public string $name = '';
    public string $description = '';
    public string $definition = '';
    public string $objectives = '';
    public array $tags = [];
    public string $imgUrl = '';
    public string $type = '';
    public string $modulesDefinition = '';
    public string $pillarsDefinition = '';
    public string $numberOfPillars = '';

    // Two-section fields
    public string $firstSectionName = '';
    public string $firstSectionDescription = '';
    public string $firstSectionDefinition = '';
    public string $firstSectionObjectives = '';
    public string $firstSectionImgUrl = '';

    public string $secondSectionName = '';
    public string $secondSectionDescription = '';
    public string $secondSectionDefinition = '';
    public string $secondSectionObjectives = '';
    public string $secondSectionImgUrl = '';

    public string $error = '';
    public bool $isEditMode = false;
    public ?int $methodologyId = null;

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|min:3',
            'description' => 'required|string|min:3',
            'definition' => 'required|string|min:3',
            'objectives' => 'nullable|string',
            'tags' => 'array',
            'tags.*' => 'integer',
            'imgUrl' => 'required|string',
            'type' => 'required|in:simple,complex,twoSection',
        ];

        if ($this->type === 'simple') {
            $rules['modulesDefinition'] = 'nullable|string';
        } elseif ($this->type === 'complex') {
            $rules['pillarsDefinition'] = 'nullable|string';
            $rules['numberOfPillars'] = 'nullable|string';
        } elseif ($this->type === 'twoSection') {
            $rules['firstSectionName'] = 'required|string|min:3';
            $rules['firstSectionDescription'] = 'required|string|min:3';
            $rules['firstSectionDefinition'] = 'required|string|min:3';
            $rules['firstSectionObjectives'] = 'nullable|string';
            $rules['firstSectionImgUrl'] = 'required|string';

            $rules['secondSectionName'] = 'required|string|min:3';
            $rules['secondSectionDescription'] = 'required|string|min:3';
            $rules['secondSectionDefinition'] = 'required|string|min:3';
            $rules['secondSectionObjectives'] = 'nullable|string';
            $rules['secondSectionImgUrl'] = 'required|string';
        }

        return $rules;
    }

    protected $listeners = [
        'reset-modal' => 'resetForm',
        'edit-methodology' => 'editMethodology',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function editMethodology(int $methodologyId)
    {
        $this->isEditMode = true;
        $this->methodologyId = $methodologyId;

        $methodology = Methodology::findOrFail($methodologyId);
        $this->name = $methodology->name;
        $this->description = $methodology->description;
        $this->definition = $methodology->definition;
        $this->objectives = $methodology->objectives ?? '';
        $this->tags = $methodology->tags ?? [];
        $this->imgUrl = $methodology->img_url ?? '';
        $this->type = $methodology->type;
        $this->modulesDefinition = $methodology->modules_definition ?? '';
        $this->pillarsDefinition = $methodology->pillars_definition ?? '';
        $this->numberOfPillars = $methodology->number_of_pillars ?? '';

        // Two-section fields
        $this->firstSectionName = $methodology->first_section_name ?? '';
        $this->firstSectionDescription = $methodology->first_section_description ?? '';
        $this->firstSectionDefinition = $methodology->first_section_definition ?? '';
        $this->firstSectionObjectives = $methodology->first_section_objectives ?? '';
        $this->firstSectionImgUrl = $methodology->first_section_img_url ?? '';

        $this->secondSectionName = $methodology->second_section_name ?? '';
        $this->secondSectionDescription = $methodology->second_section_description ?? '';
        $this->secondSectionDefinition = $methodology->second_section_definition ?? '';
        $this->secondSectionObjectives = $methodology->second_section_objectives ?? '';
        $this->secondSectionImgUrl = $methodology->second_section_img_url ?? '';

        $this->dispatch('show-modal', selector: '#methodology_add_modal');
    }

    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->definition = '';
        $this->objectives = '';
        $this->tags = [];
        $this->imgUrl = '';
        $this->type = '';
        $this->modulesDefinition = '';
        $this->pillarsDefinition = '';
        $this->numberOfPillars = '';

        // Two-section fields
        $this->firstSectionName = '';
        $this->firstSectionDescription = '';
        $this->firstSectionDefinition = '';
        $this->firstSectionObjectives = '';
        $this->firstSectionImgUrl = '';

        $this->secondSectionName = '';
        $this->secondSectionDescription = '';
        $this->secondSectionDefinition = '';
        $this->secondSectionObjectives = '';
        $this->secondSectionImgUrl = '';

        $this->error = '';
        $this->isEditMode = false;
        $this->methodologyId = null;
    }

    public function closeModal()
    {
        $this->dispatch('click');
        $this->resetForm();
    }

    public function save()
    {
        // Clear previous errors
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
                'type' => $this->type,
                'modules_definition' => $this->type === 'simple' ? $this->modulesDefinition : null,
                'pillars_definition' => $this->type === 'complex' ? $this->pillarsDefinition : null,
                'number_of_pillars' => $this->type === 'complex'
                    ? (is_numeric($this->numberOfPillars) ? (int) $this->numberOfPillars : null)
                    : null,
            ];

            if ($this->type === 'twoSection') {
                $data = array_merge($data, [
                    'first_section_name' => $this->firstSectionName,
                    'first_section_description' => $this->firstSectionDescription,
                    'first_section_definition' => $this->firstSectionDefinition,
                    'first_section_objectives' => $this->firstSectionObjectives,
                    'first_section_img_url' => $this->firstSectionImgUrl ?: null,
                    'second_section_name' => $this->secondSectionName,
                    'second_section_description' => $this->secondSectionDescription,
                    'second_section_definition' => $this->secondSectionDefinition,
                    'second_section_objectives' => $this->secondSectionObjectives,
                    'second_section_img_url' => $this->secondSectionImgUrl ?: null,
                ]);
            }

            if ($this->isEditMode) {
                $methodology = Methodology::findOrFail($this->methodologyId);

                // Preserve existing images if user didn't change them in edit mode
                $updateData = $data;
                if ($this->imgUrl === '') {
                    unset($updateData['img_url']);
                }
                if ($this->type === 'twoSection') {
                    if ($this->firstSectionImgUrl === '') {
                        unset($updateData['first_section_img_url']);
                    }
                    if ($this->secondSectionImgUrl === '') {
                        unset($updateData['second_section_img_url']);
                    }
                }

                $methodology->update($updateData);
            } else {
                $data['active'] = false; // Default status is deactivated
                $methodology = Methodology::create($data);
                $methodology->save();
            }

            $this->dispatch('refreshTable');
            $this->closeModal();
            $this->dispatch('show-toast', type: 'success', message: $this->isEditMode ? 'Methodology updated successfully!' : 'Methodology added successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Methodology save failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error = 'An error occurred while saving the methodology. Please try again.';
        }
    }

    public function render()
    {
        return view('livewire.homepage.methodologies.methodology-add-modal');
    }
}
