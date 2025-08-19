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

    // General questions meta
    public string $questionsDescription = '';
    public string $questionsEstimatedTime = '';

    public string $error = '';

    // Extra details (by type)
    public string $modulesDefinition = '';
    public string $pillarsDefinition = '';
    public string $numberOfPillars = '';
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

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3',
            'description' => 'required|string|min:3',
            'definition' => 'required|string|min:3',
            'objectives' => 'nullable|string',
            'questionsDescription' => 'nullable|string',
            'questionsEstimatedTime' => 'nullable|integer|min:0',
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

        // Prefill general questions meta
        $this->questionsDescription = $methodology->questions_description ?? '';
        $this->questionsEstimatedTime = is_numeric($methodology->questions_estimated_time ?? null)
            ? (string) ((int) $methodology->questions_estimated_time)
            : '';

        // Prefill extra details by type
        $this->modulesDefinition = $methodology->modules_definition ?? '';
        $this->pillarsDefinition = $methodology->pillars_definition ?? '';
        $this->numberOfPillars = (string)($methodology->number_of_pillars ?? '');
        $this->firstSectionName = $methodology->first_section_name ?? '';
        $this->secondSectionName = $methodology->second_section_name ?? '';

        // Prefill section 1 details
        $this->firstSectionDescription = $methodology->first_section_description ?? '';
        $this->firstSectionDefinition = $methodology->first_section_definition ?? '';
        $this->firstSectionObjectives = $methodology->first_section_objectives ?? '';
        $this->firstSectionNumberOfPillars = (string)($methodology->first_section_number_of_pillars ?? '');
        $this->firstSectionPillarsDefinition = $methodology->first_section_pillars_definition ?? '';
        $this->firstSectionImgUrl = $methodology->first_section_img_url ?? '';
        $this->firstSectionNumberOfQuestions = (string)($methodology->first_section_number_of_questions ?? '');
        $this->firstSectionMinutes = (string)($methodology->first_section_minutes ?? '');
        $this->firstSectionBrief = $methodology->first_section_brief ?? '';

        // Prefill section 2 details
        $this->secondSectionDescription = $methodology->second_section_description ?? '';
        $this->secondSectionDefinition = $methodology->second_section_definition ?? '';
        $this->secondSectionObjectives = $methodology->second_section_objectives ?? '';
        $this->secondSectionNumberOfPillars = (string)($methodology->second_section_number_of_pillars ?? '');
        $this->secondSectionPillarsDefinition = $methodology->second_section_pillars_definition ?? '';
        $this->secondSectionImgUrl = $methodology->second_section_img_url ?? '';
        $this->secondSectionNumberOfQuestions = (string)($methodology->second_section_number_of_questions ?? '');
        $this->secondSectionMinutes = (string)($methodology->second_section_minutes ?? '');
        $this->secondSectionBrief = $methodology->second_section_brief ?? '';
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
                'questions_description' => $this->questionsDescription ?: null,
                'questions_estimated_time' => is_numeric($this->questionsEstimatedTime)
                    ? (int)$this->questionsEstimatedTime
                    : null,
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
                'firstSectionDescription' => 'required|string|min:3',
                'firstSectionDefinition' => 'required|string|min:3',
                'firstSectionObjectives' => 'nullable|string',
                'firstSectionNumberOfPillars' => 'nullable|string',
                'firstSectionPillarsDefinition' => 'nullable|string',
                'firstSectionImgUrl' => 'required|string',
                'firstSectionNumberOfQuestions' => 'nullable|string',
                'firstSectionMinutes' => 'nullable|string',
                'firstSectionBrief' => 'nullable|string',
            ]);

            $methodology = Methodology::findOrFail($this->methodologyId);
            $methodology->update([
                'first_section_name' => $this->firstSectionName,
                'first_section_description' => $this->firstSectionDescription,
                'first_section_definition' => $this->firstSectionDefinition,
                'first_section_objectives' => $this->firstSectionObjectives,
                'first_section_number_of_pillars' => $this->firstSectionNumberOfPillars,
                'first_section_pillars_definition' => $this->firstSectionPillarsDefinition,
                'first_section_img_url' => $this->firstSectionImgUrl ?: null,
                'first_section_number_of_questions' => $this->firstSectionNumberOfQuestions,
                'first_section_minutes' => $this->firstSectionMinutes,
                'first_section_brief' => $this->firstSectionBrief,
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
                'secondSectionDescription' => 'required|string|min:3',
                'secondSectionDefinition' => 'required|string|min:3',
                'secondSectionObjectives' => 'nullable|string',
                'secondSectionNumberOfPillars' => 'nullable|string',
                'secondSectionPillarsDefinition' => 'nullable|string',
                'secondSectionImgUrl' => 'required|string',
                'secondSectionNumberOfQuestions' => 'nullable|string',
                'secondSectionMinutes' => 'nullable|string',
                'secondSectionBrief' => 'nullable|string',
            ]);

            $methodology = Methodology::findOrFail($this->methodologyId);
            $methodology->update([
                'second_section_name' => $this->secondSectionName,
                'second_section_description' => $this->secondSectionDescription,
                'second_section_definition' => $this->secondSectionDefinition,
                'second_section_objectives' => $this->secondSectionObjectives,
                'second_section_number_of_pillars' => $this->secondSectionNumberOfPillars,
                'second_section_pillars_definition' => $this->secondSectionPillarsDefinition,
                'second_section_img_url' => $this->secondSectionImgUrl ?: null,
                'second_section_number_of_questions' => $this->secondSectionNumberOfQuestions,
                'second_section_minutes' => $this->secondSectionMinutes,
                'second_section_brief' => $this->secondSectionBrief,
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


