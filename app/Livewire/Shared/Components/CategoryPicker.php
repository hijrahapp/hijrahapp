<?php

namespace App\Livewire\Shared\Components;

use App\Models\Category;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class CategoryPicker extends Component
{
    #[Modelable]
    public array $value = [];

    public string $query = '';

    public array $suggestions = [];

    public bool $showSuggestions = false;

    public ?string $label = 'Categories';

    public ?string $placeholder = 'Search or add categories';

    public ?string $addButtonText = 'Add';

    public bool $required = false;

    protected $listeners = [
        'reset-modal' => 'clearQuery',
    ];

    public function mount(): void
    {
        if (! is_array($this->value)) {
            $this->value = [];
        }
        $this->value = $this->normalizeIds($this->value);
    }

    public function updatedValue(): void
    {
        // Normalize inbound changes from parent (strings/objects/arrays)
        $this->value = $this->normalizeIds($this->value);
        $this->dispatch('input');
    }

    public function updatedQuery(): void
    {
        if (strlen($this->query) >= 1) {
            $this->suggestions = Category::where('name', 'like', '%'.$this->query.'%')
                ->where('active', true)
                ->limit(7)
                ->get(['id', 'name'])
                ->toArray();
            $this->showSuggestions = true;
        } else {
            $this->suggestions = [];
            $this->showSuggestions = false;
        }
    }

    public function add(): void
    {
        $name = trim($this->query);
        if ($name === '') {
            return;
        }

        $existing = Category::where('name', $name)->first();
        if ($existing) {
            $this->pushCategoryId($existing->id);
        } else {
            $new = Category::create(['name' => $name, 'active' => true]);
            $this->pushCategoryId($new->id);
        }

        $this->clearQuery();
    }

    public function select(int $categoryId, string $name): void
    {
        $this->pushCategoryId($categoryId);
        $this->clearQuery();
    }

    public function remove(int $categoryId): void
    {
        $this->value = array_values(array_filter($this->value, fn ($id) => (int) $id !== (int) $categoryId));
        $this->dispatch('input');
    }

    private function pushCategoryId(int $id): void
    {
        $id = (int) $id;
        if (! in_array($id, $this->value, true)) {
            $this->value[] = $id;
            $this->value = array_values(array_unique(array_map('intval', $this->value)));
            $this->dispatch('input');
        }
    }

    public function clearQuery(): void
    {
        $this->query = '';
        $this->suggestions = [];
        $this->showSuggestions = false;
    }

    private function normalizeIds($items): array
    {
        if (empty($items)) {
            return [];
        }
        $ids = [];
        foreach ((array) $items as $item) {
            if (is_numeric($item)) {
                $ids[] = (int) $item;
            } elseif (is_array($item)) {
                $cand = $item['id'] ?? $item['value'] ?? null;
                if ($cand !== null && is_numeric($cand)) {
                    $ids[] = (int) $cand;
                }
            } elseif (is_object($item)) {
                $cand = $item->id ?? $item->value ?? null;
                if ($cand !== null && is_numeric($cand)) {
                    $ids[] = (int) $cand;
                }
            }
        }

        return array_values(array_unique(array_filter($ids, fn ($v) => $v > 0)));
    }

    #[Computed]
    public function selected(): array
    {
        if (empty($this->value)) {
            return [];
        }

        return Category::whereIn('id', $this->value)
            ->pluck('name', 'id')
            ->toArray();
    }

    public function render()
    {
        return view('livewire.shared.components.category-picker');
    }
}
