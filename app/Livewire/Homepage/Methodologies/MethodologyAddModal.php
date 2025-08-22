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
//    public string $modulesDefinition = '';
//    public string $pillarsDefinition = '';
//    public string $numberOfPillars = '';

    // General questions meta
    public string $questionsDescription = '';
    public string $questionsEstimatedTime = '';

    // Two-section fields
//    public string $firstSectionName = '';
//    public string $secondSectionName = '';

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
            'questionsDescription' => 'nullable|string',
            'questionsEstimatedTime' => 'nullable|integer|min:0',
            'tags' => 'array',
            'tags.*' => 'integer',
            // Required on create, nullable on edit
            'imgUrl' => $this->isEditMode ? 'nullable|string' : 'required|string',
            'type' => 'required|in:simple,complex,twoSection',
        ];

//        if ($this->type === 'simple') {
//            $rules['modulesDefinition'] = 'nullable|string';
//        } elseif ($this->type === 'complex') {
//            $rules['pillarsDefinition'] = 'nullable|string';
//            $rules['numberOfPillars'] = 'nullable|string';
//        } elseif ($this->type === 'twoSection') {
//            $rules['firstSectionName'] = 'required|string|min:3';
//            $rules['secondSectionName'] = 'required|string|min:3';
//        }

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
//        $this->modulesDefinition = $methodology->modules_definition ?? '';
//        $this->pillarsDefinition = $methodology->pillars_definition ?? '';
//        $this->numberOfPillars = $methodology->number_of_pillars ?? '';

        // General questions meta
        $this->questionsDescription = $methodology->questions_description ?? '';
        $this->questionsEstimatedTime = is_numeric($methodology->questions_estimated_time ?? null)
            ? (string) ((int) $methodology->questions_estimated_time)
            : '';

        // Two-section fields
//        $this->firstSectionName = $methodology->first_section_name ?? '';
//        $this->secondSectionName = $methodology->second_section_name ?? '';

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
//        $this->modulesDefinition = '';
//        $this->pillarsDefinition = '';
//        $this->numberOfPillars = '';

        // General questions meta
        $this->questionsDescription = '';
        $this->questionsEstimatedTime = '';

        // Two-section fields
//        $this->firstSectionName = '';
//        $this->secondSectionName = '';

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
                'questions_description' => $this->questionsDescription ?: null,
                'questions_estimated_time' => is_numeric($this->questionsEstimatedTime)
                    ? (int) $this->questionsEstimatedTime
                    : null,
                'tags' => $this->tags,
                'img_url' => $this->imgUrl ?: null,
                'type' => $this->type,
//                'modules_definition' => $this->type === 'simple' ? $this->modulesDefinition : null,
//                'pillars_definition' => $this->type === 'complex' ? $this->pillarsDefinition : null,
//                'number_of_pillars' => $this->type === 'complex'
//                    ? (is_numeric($this->numberOfPillars) ? (int) $this->numberOfPillars : null)
//                    : null,
            ];

//            if ($this->type === 'twoSection') {
//                $data = array_merge($data, [
//                    'first_section_name' => $this->firstSectionName,
//                    'second_section_name' => $this->secondSectionName,
//                ]);
//            }

            if ($this->isEditMode) {
                $methodology = Methodology::findOrFail($this->methodologyId);
                $methodology->update($data);
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
