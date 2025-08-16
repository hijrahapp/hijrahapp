<?php

namespace App\Livewire\Shared\Components;

use Illuminate\Support\Str;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class RichText extends Component
{
    #[Modelable]
    public string $model = '';

    public string $placeholder = '';
    public string $editorId = '';
    public string $minHeight = '150px';

    public function mount(string $placeholder = '', ?string $editorId = null, string $minHeight = '150px')
    {
        $this->placeholder = $placeholder ?: '';
        $this->editorId = $editorId ?: ('richtext_' . Str::uuid()->toString());
        $this->minHeight = $minHeight;
    }

    public function render()
    {
        return view('livewire.shared.components.rich-text');
    }
}


