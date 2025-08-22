<?php

namespace App\Livewire\Shared\Components;

use Livewire\Attributes\Modelable;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class ImagePicker extends Component
{
    use WithFileUploads;
    #[Modelable]
    public string $value = '';

    public string $placeholder = '/assets/media/avatars/blank.png';
    public string $label = 'Image';
    public bool $required = false;
    public $file = null; // Temporary uploaded file

    public function mount(
        string $placeholder = '/assets/media/avatars/blank.png',
        string $label = 'Image',
        bool $required = false
    ): void {
        $this->placeholder = $placeholder;
        $this->label = $label;
        $this->required = $required;
    }

    public function clearImage(): void
    {
        $this->value = '';
    }

    public function updatedFile(): void
    {
        $this->validate([
            'file' => 'image|max:2048', // max 2MB
        ]);

        $path = $this->file->store('uploads/images', 'public');

        // Resolve paths
        $sourcePath = Storage::disk('public')->path($path);
        $publicStorageRoot = public_path('storage');
        $destinationPath = $publicStorageRoot . DIRECTORY_SEPARATOR . $path;

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
        $this->value = asset('storage/' . $path);
        $this->file = null;
    }

    public function render()
    {
        return view('livewire.shared.components.image-picker');
    }
}
