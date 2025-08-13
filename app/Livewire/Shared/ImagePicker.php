<?php

namespace App\Livewire\Shared;

use Livewire\Attributes\Modelable;
use Livewire\Component;

class ImagePicker extends Component
{
    #[Modelable]
    public string $value = '';

    public string $placeholder = '/assets/media/avatars/blank.png';
    public string $label = 'Image';
    public bool $required = false;

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

    public function render()
    {
        return view('livewire.shared.image-picker');
    }
}