<?php

namespace App\Livewire\Shared\Components;

use Livewire\Component;

class ListInput extends Component
{
    public array $items = [];

    public string $currentInput = '';

    public string $label = '';

    public string $placeholder = 'Enter item';

    public string $addButtonText = 'Add';

    public bool $required = false;

    public string $identifier = '';

    public bool $reorderEnabled = false;

    public function mount($items = [], $label = '', $placeholder = 'Enter item', $addButtonText = 'Add', $required = false, $identifier = '', $reorderEnabled = false)
    {
        $this->items = is_array($items) ? $items : [];
        $this->label = $label;
        $this->placeholder = $placeholder;
        $this->addButtonText = $addButtonText;
        $this->required = $required;
        $this->identifier = $identifier;
        $this->reorderEnabled = $reorderEnabled;
    }

    public function addItem()
    {
        $item = trim($this->currentInput);

        if ($item && ! in_array($item, $this->items)) {
            $this->items[] = $item;
            $this->currentInput = '';
            $this->dispatchUpdate();
        }
    }

    public function removeItem($index)
    {
        if (isset($this->items[$index])) {
            unset($this->items[$index]);
            $this->items = array_values($this->items); // Re-index array
            $this->dispatchUpdate();
        }
    }

    public function updatedItems()
    {
        // Called when any item in the array is updated via wire:model
        $this->dispatchUpdate();
    }

    public function moveUp($index)
    {
        if ($index > 0 && isset($this->items[$index])) {
            $temp = $this->items[$index];
            $this->items[$index] = $this->items[$index - 1];
            $this->items[$index - 1] = $temp;
            $this->dispatchUpdate();
        }
    }

    public function moveDown($index)
    {
        if ($index < count($this->items) - 1 && isset($this->items[$index])) {
            $temp = $this->items[$index];
            $this->items[$index] = $this->items[$index + 1];
            $this->items[$index + 1] = $temp;
            $this->dispatchUpdate();
        }
    }

    private function dispatchUpdate()
    {
        if ($this->identifier) {
            $this->dispatch('list-updated', identifier: $this->identifier, items: $this->items);
        } else {
            $this->dispatch('list-updated', items: $this->items);
        }
    }

    public function render()
    {
        return view('livewire.shared.components.list-input');
    }
}
