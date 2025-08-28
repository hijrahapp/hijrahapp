<?php

namespace App\Livewire\Shared\Components;

use Livewire\Attributes\Modelable;
use Livewire\Component;

class Textarea extends Component
{
    #[Modelable]
    public string $model = '';

    public string $label = '';

    public string $placeholder = '';

    public int $rows = 3;

    public bool $required = false;

    public ?string $hint = null;

    public ?int $maxlength = null;

    public function mount(
        string $label = '',
        string $placeholder = '',
        int $rows = 3,
        bool $required = false,
        ?string $hint = null,
        ?int $maxlength = null,
    ): void {
        $this->label = $label;
        $this->placeholder = $placeholder;
        $this->rows = $rows;
        $this->required = $required;
        $this->hint = $hint;
        $this->maxlength = $maxlength;
    }

    public function render()
    {
        return view('livewire.shared.components.textarea');
    }
}
