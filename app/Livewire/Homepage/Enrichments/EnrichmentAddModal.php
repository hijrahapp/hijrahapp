<?php

namespace App\Livewire\Homepage\Enrichments;

use App\Models\Enrichment;
use Livewire\Attributes\Computed;
use Livewire\Component;

class EnrichmentAddModal extends Component
{
    public string $title = '';

    public string $description = '';

    public string $imgUrl = '';

    public string $type = '';

    // public string $header = '';

    public array $categories = [];

    public array $interests = [];

    public array $tags = [];

    public string $error = '';

    protected function rules()
    {
        $rules = [
            'title' => 'required|string|min:3|max:100',
            'description' => 'required|string|min:3|max:200',
            'imgUrl' => 'required|string',
            'type' => 'required|in:article,video,audio,book,short-video',
            'categories' => 'required|array|min:1',
            'categories.*' => 'integer',
            'interests' => 'required|array|min:1',
            'interests.*' => 'integer',
            'tags' => 'array',
            'tags.*' => 'integer',
            // 'header' => 'nullable|string',
        ];

        return $rules;
    }

    protected $listeners = [
        'reset-modal' => 'resetForm',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    #[Computed]
    public function enrichmentTypes()
    {
        return [
            'article' => 'Article',
            'video' => 'Video',
            'audio' => 'Audio',
            'book' => 'Book',
            'short-video' => 'Short Video',
        ];
    }

    public function resetForm()
    {
        $this->title = '';
        $this->description = '';
        $this->imgUrl = '';
        $this->type = '';
        // $this->header = '';
        $this->categories = [];
        $this->interests = [];
        $this->tags = [];
        $this->error = '';
        $this->resetValidation();
    }

    public function save(bool $addAnother = false)
    {
        // Clear previous errors
        $this->resetErrorBag();

        try {
            $this->validate();

            $data = [
                'title' => $this->title,
                'description' => $this->description,
                'img_url' => $this->imgUrl ?: null,
                'type' => $this->type,
                // 'header' => $this->header ?: null,
                // 'content' => null, // Content will be managed in the manage page
                // 'content_url' => null, // Content URL will be managed in the manage page
                // 'content_image' => null, // Content image will be managed in the manage page
                'categories' => $this->categories,
                'interests' => $this->interests,
                'tags' => $this->tags,
                'active' => false,
            ];

            Enrichment::create($data);

            $this->dispatch('refreshTable');
            $this->dispatch('show-toast',
                type: 'success',
                message: 'Enrichment created successfully! You can now manage its content details.'
            );

            if ($addAnother) {
                $this->resetForm();
                // Keep modal open for adding another
            } else {
                $this->dispatch('click');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions so they show in the form
            throw $e;
        } catch (\Exception $e) {
            $this->error = 'An error occurred while saving the enrichment: '.$e->getMessage();
            logger()->error('Enrichment save error: '.$e->getMessage(), [
                'enrichment_data' => [
                    'title' => $this->title,
                    'type' => $this->type,
                    'categories' => $this->categories,
                    'interests' => $this->interests,
                ],
            ]);
        }
    }

    public function closeModal()
    {
        $this->resetForm();
        $this->dispatch('click');
    }

    public function render()
    {
        return view('livewire.homepage.enrichments.enrichment-add-modal');
    }
}
