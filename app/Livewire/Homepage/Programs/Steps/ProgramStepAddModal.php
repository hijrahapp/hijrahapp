<?php

namespace App\Livewire\Homepage\Programs\Steps;

use App\Models\Step;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProgramStepAddModal extends Component
{
    use WithFileUploads;

    public ?int $programId = null;

    public ?int $stepId = null;

    public bool $isEditMode = false;

    // Basic fields
    public string $name = '';

    public string $type = '';

    public int $timeToFinish = 1;

    public string $timeType = 'minutes';

    // Individual column fields
    public string $header = '';

    public string $content = '';

    public string $description = '';

    public string $contentUrl = '';

    public string $contentImage = '';

    public array $advices = [];

    public array $challenges = [];

    public string $error = '';

    protected $listeners = [
        'open-add-step-modal' => 'openAddModal',
        'edit-step' => 'editStep',
        'reset-step-modal' => 'resetForm',
        'list-updated' => 'handleListUpdate',
    ];

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|min:3|max:255',
            'type' => 'required|string|in:'.implode(',', array_keys(Step::TYPES)),
            'timeToFinish' => 'required|integer|min:1',
            'timeType' => 'required|string|in:'.implode(',', array_keys(Step::TIME_TYPES)),
        ];

        // Add type-specific validation rules
        if ($this->type) {
            $requiredFields = $this->getRequiredFieldsForType($this->type);

            foreach ($requiredFields as $field) {
                switch ($field) {
                    case 'header':
                        $rules['header'] = 'required|string|min:3|max:255';
                        break;
                    case 'content':
                        $rules['content'] = 'required|string|min:10';
                        break;
                    case 'description':
                        $rules['description'] = 'nullable|string|min:10|max:1000';
                        break;
                    case 'content_url':
                        $rules['contentUrl'] = 'nullable|url|max:500';
                        break;
                    case 'content_image':
                        $rules['contentImage'] = 'required|url|max:500';
                        break;
                    case 'advices':
                        $rules['advices'] = 'required|array|min:1';
                        $rules['advices.*'] = 'required|string|min:5|max:500';
                        break;
                    case 'challenges':
                        $rules['challenges'] = 'required|array|min:1';
                        $rules['challenges.*'] = 'required|string|min:5|max:500';
                        break;
                }
            }
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if content_url is required but not provided
            if (in_array($this->type, ['video', 'audio', 'book'])) {
                if (empty($this->contentUrl)) {
                    $validator->errors()->add('contentUrl', 'Either a content URL or file upload is required for this step type.');
                }
            }
        });
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'The step name is required.',
            'name.min' => 'The step name must be at least 3 characters.',
            'name.max' => 'The step name may not be greater than 255 characters.',
            'type.required' => 'Please select a step type.',
            'type.in' => 'Please select a valid step type.',
            'timeToFinish.required' => 'The duration is required.',
            'timeToFinish.integer' => 'The duration must be a number.',
            'timeToFinish.min' => 'The duration must be at least 1.',
            'timeType.required' => 'Please select a time unit.',
            'timeType.in' => 'Please select a valid time unit.',
            'header.required' => 'The header is required for this step type.',
            'header.min' => 'The header must be at least 3 characters.',
            'header.max' => 'The header may not be greater than 255 characters.',
            'content.required' => 'The content is required for this step type.',
            'content.min' => 'The content must be at least 10 characters.',
            'description.min' => 'The description must be at least 10 characters.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'contentUrl.required' => 'The content URL is required for this step type.',
            'contentUrl.url' => 'Please enter a valid URL.',
            'contentUrl.max' => 'The content URL may not be greater than 500 characters.',
            'contentImage.required' => 'The cover image is required for this step type.',
            'contentImage.url' => 'Please enter a valid image URL.',
            'contentImage.max' => 'The image URL may not be greater than 500 characters.',
            'advices.required' => 'At least one advice is required for this step type.',
            'advices.array' => 'Advices must be provided as a list.',
            'advices.min' => 'At least one advice is required.',
            'advices.*.required' => 'Each advice is required.',
            'advices.*.min' => 'Each advice must be at least 5 characters.',
            'advices.*.max' => 'Each advice may not be greater than 500 characters.',
            'challenges.required' => 'At least one challenge is required for this step type.',
            'challenges.array' => 'Challenges must be provided as a list.',
            'challenges.min' => 'At least one challenge is required.',
            'challenges.*.required' => 'Each challenge is required.',
            'challenges.*.min' => 'Each challenge must be at least 5 characters.',
            'challenges.*.max' => 'Each challenge may not be greater than 500 characters.',
        ];
    }

    public function openAddModal($programId)
    {
        $this->resetForm();
        $this->programId = $programId;
        $this->dispatch('show-modal', selector: '#program_step_add_modal');
    }

    public function editStep($stepId)
    {
        $this->resetForm();
        $step = Step::find($stepId);

        if ($step) {
            $this->isEditMode = true;
            $this->stepId = $step->id;
            $this->programId = $step->program_id;
            $this->name = $step->name;
            $this->type = $step->type;
            $this->timeToFinish = $step->time_to_finish;
            $this->timeType = $step->time_type;

            $this->loadTypeSpecificData($step);
        }
        $this->dispatch('show-modal', selector: '#program_step_add_modal');
    }

    protected function loadTypeSpecificData(Step $step)
    {
        // Load data from individual columns
        $this->header = $step->header ?? '';
        $this->content = $step->content ?? '';
        $this->description = $step->description ?? '';
        $this->contentUrl = $step->content_url ?? '';
        $this->contentImage = $step->content_image ?? '';
        $this->advices = $step->advices ?? [];
        $this->challenges = $step->challenges ?? [];
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'program_id' => $this->programId,
                'name' => $this->name,
                'type' => $this->type,
                'time_to_finish' => $this->timeToFinish,
                'time_type' => $this->timeType,
                'header' => $this->header,
                'content' => $this->content,
                'description' => $this->description,
                'content_url' => $this->contentUrl,
                'content_image' => $this->contentImage,
                'advices' => $this->advices,
                'challenges' => $this->challenges,
            ];

            if ($this->isEditMode && $this->stepId) {
                $step = Step::find($this->stepId);
                $step->update($data);
                $message = 'Step updated successfully';
            } else {
                Step::create($data);
                $message = 'Step created successfully';
            }

            $this->dispatch('show-toast', type: 'success', message: $message);

            $this->dispatch('refreshTable');
            $this->dispatch('click');
            $this->resetForm();
        } catch (\Exception $e) {
            $this->error = 'Failed to save step: '.$e->getMessage();
            $this->dispatch('show-toast', type: 'error', message: $this->error);
        }
    }

    public function updateAdvices($advices)
    {
        $this->advices = $advices;
    }

    public function updateChallenges($challenges)
    {
        $this->challenges = $challenges;
    }

    #[On('list-updated')]
    public function handleListUpdate($identifier = null, $items = [])
    {
        if ($identifier === 'advices') {
            $this->advices = $items;
        } elseif ($identifier === 'challenges') {
            $this->challenges = $items;
        }
    }

    /**
     * Get required fields for a specific step type
     */
    protected function getRequiredFieldsForType(string $type): array
    {
        return match ($type) {
            'journal' => ['header'],
            'article' => ['header', 'content'],
            'advice' => ['header', 'advices'],
            'daily_mission' => ['header', 'content'],
            'quiz' => [], // Questions will be managed separately
            'video', 'audio' => ['content_url'],
            'book' => ['content_url', 'content_image'],
            'challenge' => ['header', 'challenges'],
            default => [],
        };
    }

    public function resetForm()
    {
        $this->reset([
            'programId', 'stepId', 'isEditMode', 'name', 'type',
            'timeToFinish', 'timeType', 'header', 'content',
            'description', 'contentUrl', 'contentImage', 'advices',
            'challenges', 'error',
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.homepage.programs.steps.program-step-add-modal', [
            'stepTypes' => Step::TYPES,
            'timeTypes' => Step::TIME_TYPES,
        ]);
    }
}
