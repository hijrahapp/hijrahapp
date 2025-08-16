<?php

namespace App\Livewire\Homepage\Methodologies;

use App\Models\Methodology;
use Livewire\Component;

class MethodologySectionsModal extends Component
{
    public ?int $methodologyId = null;
    public string $methodologyName = '';
    public string $firstSectionName = '';
    public string $secondSectionName = '';

    // Section 1 fields
    public string $firstSectionDescription = '';
    public string $firstSectionDefinition = '';
    public string $firstSectionObjectives = '';
    public string $firstSectionNumberOfPillars = '';
    public string $firstSectionPillarsDefinition = '';
    public string $firstSectionImgUrl = '';
    public string $firstSectionNumberOfQuestions = '';
    public string $firstSectionMinutes = '';
    public string $firstSectionBrief = '';

    // Section 2 fields
    public string $secondSectionDescription = '';
    public string $secondSectionDefinition = '';
    public string $secondSectionObjectives = '';
    public string $secondSectionNumberOfPillars = '';
    public string $secondSectionPillarsDefinition = '';
    public string $secondSectionImgUrl = '';
    public string $secondSectionNumberOfQuestions = '';
    public string $secondSectionMinutes = '';
    public string $secondSectionBrief = '';

    public string $error = '';

    protected function rules()
    {
        return [
            'firstSectionDescription' => 'required|string|min:3',
            'firstSectionDefinition' => 'required|string|min:3',
            'firstSectionObjectives' => 'nullable|string',
            'firstSectionNumberOfPillars' => 'nullable|string',
            'firstSectionPillarsDefinition' => 'nullable|string',
            'firstSectionImgUrl' => 'required|string',
            'firstSectionNumberOfQuestions' => 'nullable|string',
            'firstSectionMinutes' => 'nullable|string',
            'firstSectionBrief' => 'nullable|string',

            'secondSectionDescription' => 'required|string|min:3',
            'secondSectionDefinition' => 'required|string|min:3',
            'secondSectionObjectives' => 'nullable|string',
            'secondSectionNumberOfPillars' => 'nullable|string',
            'secondSectionPillarsDefinition' => 'nullable|string',
            'secondSectionImgUrl' => 'required|string',
            'secondSectionNumberOfQuestions' => 'nullable|string',
            'secondSectionMinutes' => 'nullable|string',
            'secondSectionBrief' => 'nullable|string',
        ];
    }

    protected $listeners = [
        'manage-sections' => 'loadMethodology',
    ];

    public function loadMethodology(int $methodologyId)
    {
        $this->methodologyId = $methodologyId;
        $methodology = Methodology::findOrFail($methodologyId);

        if ($methodology->type !== 'twoSection') {
            $this->error = 'This methodology is not a two-section type.';
            return;
        }

        $this->methodologyName = $methodology->name;
        $this->firstSectionName = $methodology->first_section_name ?? 'Section 1';
        $this->secondSectionName = $methodology->second_section_name ?? 'Section 2';

        // Load Section 1 data
        $this->firstSectionDescription = $methodology->first_section_description ?? '';
        $this->firstSectionDefinition = $methodology->first_section_definition ?? '';
        $this->firstSectionObjectives = $methodology->first_section_objectives ?? '';
        $this->firstSectionNumberOfPillars = $methodology->first_section_number_of_pillars ?? '';
        $this->firstSectionPillarsDefinition = $methodology->first_section_pillars_definition ?? '';
        $this->firstSectionImgUrl = $methodology->first_section_img_url ?? '';
        $this->firstSectionNumberOfQuestions = $methodology->first_section_number_of_questions ?? '';
        $this->firstSectionMinutes = $methodology->first_section_minutes ?? '';
        $this->firstSectionBrief = $methodology->first_section_brief ?? '';

        // Load Section 2 data
        $this->secondSectionDescription = $methodology->second_section_description ?? '';
        $this->secondSectionDefinition = $methodology->second_section_definition ?? '';
        $this->secondSectionObjectives = $methodology->second_section_objectives ?? '';
        $this->secondSectionNumberOfPillars = $methodology->second_section_number_of_pillars ?? '';
        $this->secondSectionPillarsDefinition = $methodology->second_section_pillars_definition ?? '';
        $this->secondSectionImgUrl = $methodology->second_section_img_url ?? '';
        $this->secondSectionNumberOfQuestions = $methodology->second_section_number_of_questions ?? '';
        $this->secondSectionMinutes = $methodology->second_section_minutes ?? '';
        $this->secondSectionBrief = $methodology->second_section_brief ?? '';

        $this->error = '';
        $this->dispatch('show-modal', selector: '#methodology_sections_modal');
    }

    public function closeModal()
    {
        $this->dispatch('click');
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->methodologyId = null;
        $this->methodologyName = '';
        $this->firstSectionName = '';
        $this->secondSectionName = '';

        // Reset Section 1 fields
        $this->firstSectionDescription = '';
        $this->firstSectionDefinition = '';
        $this->firstSectionObjectives = '';
        $this->firstSectionNumberOfPillars = '';
        $this->firstSectionPillarsDefinition = '';
        $this->firstSectionImgUrl = '';
        $this->firstSectionNumberOfQuestions = '';
        $this->firstSectionMinutes = '';
        $this->firstSectionBrief = '';

        // Reset Section 2 fields
        $this->secondSectionDescription = '';
        $this->secondSectionDefinition = '';
        $this->secondSectionObjectives = '';
        $this->secondSectionNumberOfPillars = '';
        $this->secondSectionPillarsDefinition = '';
        $this->secondSectionImgUrl = '';
        $this->secondSectionNumberOfQuestions = '';
        $this->secondSectionMinutes = '';
        $this->secondSectionBrief = '';

        $this->error = '';
    }

    public function save()
    {
        $this->resetErrorBag();

        try {
            $this->validate();

            $methodology = Methodology::findOrFail($this->methodologyId);

            $updateData = [
                'first_section_description' => $this->firstSectionDescription,
                'first_section_definition' => $this->firstSectionDefinition,
                'first_section_objectives' => $this->firstSectionObjectives,
                'first_section_number_of_pillars' => $this->firstSectionNumberOfPillars,
                'first_section_pillars_definition' => $this->firstSectionPillarsDefinition,
                'first_section_img_url' => $this->firstSectionImgUrl ?: null,
                'first_section_number_of_questions' => $this->firstSectionNumberOfQuestions,
                'first_section_minutes' => $this->firstSectionMinutes,
                'first_section_brief' => $this->firstSectionBrief,

                'second_section_description' => $this->secondSectionDescription,
                'second_section_definition' => $this->secondSectionDefinition,
                'second_section_objectives' => $this->secondSectionObjectives,
                'second_section_number_of_pillars' => $this->secondSectionNumberOfPillars,
                'second_section_pillars_definition' => $this->secondSectionPillarsDefinition,
                'second_section_img_url' => $this->secondSectionImgUrl ?: null,
                'second_section_number_of_questions' => $this->secondSectionNumberOfQuestions,
                'second_section_minutes' => $this->secondSectionMinutes,
                'second_section_brief' => $this->secondSectionBrief,
            ];

            $methodology->update($updateData);

            $this->dispatch('refreshTable');
            $this->closeModal();
            $this->dispatch('show-toast', type: 'success', message: __('messages.sections_updated_successfully'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Methodology sections save failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error = 'An error occurred while saving the sections. Please try again.';
        }
    }

    public function render()
    {
        return view('livewire.homepage.methodologies.methodology-sections-modal');
    }
}
