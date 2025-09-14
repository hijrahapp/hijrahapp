<?php

namespace App\Livewire\Shared\Components;

use Livewire\Attributes\Modelable;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class FilePicker extends Component
{
    use WithFileUploads;

    #[Modelable]
    public string $value = '';

    public string $label = 'File';

    public bool $required = false;

    public string $placeholder = 'Enter file URL or upload a file';

    public string $contentUrl = '';

    public $file = null;

    public array $allowedTypes = [];

    public int $maxSize = 10; // 10MB

    public string $helpText = '';

    protected $listeners = [
        'refreshFilePicker' => 'refreshComponent',
    ];

    protected $rules = [];

    protected $messages = [];

    public function mount(
        string $label = 'File',
        bool $required = false,
        string $placeholder = 'Enter file URL or upload a file',
        array $allowedTypes = [],
        int $maxSize = 10,
        string $helpText = '',
    ) {
        $this->label = $label;
        $this->required = $required;
        $this->placeholder = $placeholder;
        $this->allowedTypes = $allowedTypes;
        $this->maxSize = $maxSize;
        $this->helpText = $helpText;

        // Initialize contentUrl from value if provided
        if (! empty($this->value)) {
            $this->contentUrl = $this->value;
        }

        // Livewire will handle validation through global rules
    }

    public function updatedFile()
    {
        if ($this->file) {
            // Clear URL when file is uploaded
            $this->contentUrl = '';
            // Store the file and update contentUrl with the file URL  
            $this->storeFileAndUpdateUrl();
            // Update parent component value
            $this->value = $this->contentUrl;
        }
    }

    public function updatedContentUrl()
    {
        // This method is called when the contentUrl is updated
        // Clear the file when URL is manually entered
        if (! empty($this->contentUrl) && $this->file) {
            $this->file = null;
        }

        // Update parent component value
        $this->value = $this->contentUrl;
    }

    protected function storeFileAndUpdateUrl()
    {
        if ($this->file) {
            // Store the uploaded file and get its path (same as ImagePicker)
            $filePath = $this->file->store('uploads/files', 'public');

            // Resolve paths
            $sourcePath = Storage::disk('public')->path($filePath);
            $publicStorageRoot = public_path('storage');
            $destinationPath = $publicStorageRoot . DIRECTORY_SEPARATOR . $filePath;

            // Determine if public/storage is a symlink to storage/app/public.
            // If they resolve to the same real path, copying is unnecessary and unlinking would delete the same file.
            $storagePublicRoot = storage_path('app/public');
            $isSameTarget = realpath($publicStorageRoot) && realpath($publicStorageRoot) === realpath($storagePublicRoot);

            if (!$isSameTarget) {
                if (!file_exists(dirname($destinationPath))) {
                    mkdir(dirname($destinationPath), 0755, true);
                }
                // Copy then remove source
                if (@copy($sourcePath, $destinationPath)) {
                    @unlink($sourcePath);
                }
            }

            // Save URL (works whether symlinked or physically copied)
            $this->contentUrl = asset('storage/' . $filePath);
        }
    }



    public function clearFile()
    {
        $this->file = null;
        $this->contentUrl = '';
        $this->value = '';
        $this->resetErrorBag('file');
    }

    public function clearUrl()
    {
        $this->contentUrl = '';
        $this->value = '';
    }

    public function clear()
    {
        $this->file = null;
        $this->contentUrl = '';
        $this->value = '';
        $this->resetErrorBag('file');
    }

    public function getFileSize()
    {
        if ($this->file) {
            return $this->formatFileSize($this->file->getSize());
        }

        return null;
    }

    public function getFileName()
    {
        if ($this->file) {
            return $this->file->getClientOriginalName();
        }

        return null;
    }

    public function getFileType()
    {
        if ($this->file) {
            return strtoupper($this->file->getClientOriginalExtension());
        }

        return null;
    }

    public function getFileUrl()
    {
        if ($this->file) {
            return $this->file->temporaryUrl();
        }

        return $this->contentUrl;
    }

    public function refreshComponent()
    {
        // This method can be called to refresh the component
        // when the parent component updates the value
    }

    protected function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }

    public function render()
    {
        return view('livewire.shared.components.file-picker');
    }
}
