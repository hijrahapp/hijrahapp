<?php

namespace App\Livewire\Homepage\Enrichments;

use App\Models\Enrichment;
use App\Rules\FileUrlValidation;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class EnrichmentManage extends Component
{
    public ?int $enrichmentId = null;

    // Enrichment basic details
    public string $title = '';

    public string $description = '';

    public string $imgUrl = '';

    public string $type = '';

    // public string $header = '';

    public array $categories = [];

    public array $interests = [];

    public array $tags = [];

    // Content details
    public string $content = '';

    public string $contentUrl = '';

    public string $contentImage = '';

    // Dirty state flags per section
    public bool $isBasicDirty = false;

    public bool $isContentDirty = false;

    public string $error = '';

    protected function rules(): array
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
            'header' => 'nullable|string',
        ];

        // Add conditional content rules based on type
        if ($this->type === 'article') {
            $rules['content'] = 'required|string|min:3';
        } elseif (in_array($this->type, ['video', 'audio', 'book', 'short-video'])) {
            $validationRule = match ($this->type) {
                'video', 'short-video' => FileUrlValidation::video(),
                'audio' => FileUrlValidation::audio(),
                'book' => FileUrlValidation::book(),
                default => null,
            };

            $rules['contentUrl'] = array_filter([
                'required',
                'max:500',
                $validationRule,
            ]);

            $rules['contentImage'] = 'required|url|max:500';
        }

        return $rules;
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

    public function mount(int $enrichmentId)
    {
        if (! session('jwt_token')) {
            return redirect()->route('login');
        }

        $this->enrichmentId = $enrichmentId;

        $enrichment = Enrichment::findOrFail($enrichmentId);
        $this->title = $enrichment->title;
        $this->description = $enrichment->description;
        $this->imgUrl = $enrichment->img_url ?? '';
        $this->type = $enrichment->type;
        $this->header = $enrichment->header ?? '';
        $this->categories = $enrichment->categories ?? [];
        $this->interests = $enrichment->interests ?? [];
        $this->tags = $enrichment->tags ?? [];

        // Content fields
        $this->content = $enrichment->content ?? '';
        $this->contentUrl = $enrichment->content_url ?? '';
        $this->contentImage = $enrichment->content_image ?? '';
    }

    public function saveBasicDetails()
    {
        $this->resetErrorBag();

        try {
            // Validate basic fields only
            $basicRules = [
                'title' => 'required|string|min:3|max:100',
                'description' => 'required|string|min:3|max:200',
                'imgUrl' => 'required|string',
                'categories' => 'required|array|min:1',
                'categories.*' => 'integer',
                'interests' => 'required|array|min:1',
                'interests.*' => 'integer',
                'tags' => 'array',
                'tags.*' => 'integer',
            ];

            $this->validate($basicRules);

            $data = [
                'title' => $this->title,
                'description' => $this->description,
                'img_url' => $this->imgUrl,
                'categories' => $this->categories,
                'interests' => $this->interests,
                'tags' => $this->tags,
            ];

            $enrichment = Enrichment::findOrFail($this->enrichmentId);
            $enrichment->update($data);

            $this->isBasicDirty = false;
            $this->dispatch('section-saved', section: 'basic');
            $this->dispatch('show-toast', type: 'success', message: 'Enrichment details saved successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = $e->validator->errors()->first() ?: 'Please check the form for errors.';
            $this->dispatch('show-toast', type: 'error', message: $firstError);
            throw $e;
        } catch (\Throwable $e) {
            $this->error = 'An unexpected error occurred. Please try again.';
            $this->dispatch('show-toast', type: 'error', message: $this->error);
        }
    }

    public function saveContentDetails()
    {
        $this->resetErrorBag();

        try {
            // Validate content fields based on type
            $contentRules = [
                // 'header' => 'nullable|string',
            ];

            if ($this->type === 'article') {
                $contentRules['content'] = 'required|string|min:3';
            } elseif (in_array($this->type, ['video', 'audio', 'book', 'short-video'])) {
                $validationRule = match ($this->type) {
                    'video', 'short-video' => FileUrlValidation::video(),
                    'audio' => FileUrlValidation::audio(),
                    'book' => FileUrlValidation::book(),
                    default => null,
                };

                $contentRules['contentUrl'] = array_filter([
                    'required',
                    'max:500',
                    $validationRule,
                ]);

                $contentRules['contentImage'] = 'required|url|max:500';
            }

            $this->validate($contentRules);

            $data = [
                // 'header' => $this->header ?: null,
                'content' => $this->content ?: null,
                'content_url' => $this->contentUrl ?: null,
                'content_image' => $this->contentImage ?: null,
            ];

            $enrichment = Enrichment::findOrFail($this->enrichmentId);
            $enrichment->update($data);

            $this->isContentDirty = false;
            $this->dispatch('section-saved', section: 'content');
            $this->dispatch('show-toast', type: 'success', message: 'Content details saved successfully!');
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
        return redirect()->route('enrichments');
    }

    public function updated(string $property, mixed $value): void
    {
        // Basic information fields
        if (in_array($property, ['title', 'description', 'imgUrl', 'categories', 'interests', 'tags'], true)) {
            $this->isBasicDirty = true;

            return;
        }

        // Content fields
        if (in_array($property, ['header', 'content', 'contentUrl', 'contentImage'], true)) {
            $this->isContentDirty = true;

            return;
        }
    }

    public function render()
    {
        $enrichment = Enrichment::findOrFail($this->enrichmentId);

        return view('livewire.homepage.enrichments.enrichment-manage', [
            'enrichment' => $enrichment,
        ]);
    }
}
