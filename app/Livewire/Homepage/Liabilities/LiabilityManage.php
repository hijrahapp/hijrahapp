<?php

namespace App\Livewire\Homepage\Liabilities;

use App\Models\Liability;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class LiabilityManage extends Component
{
    public ?Liability $liability = null;

    public string $name = '';

    public string $description = '';

    public string $title = '';

    public string $header = '';

    public array $todos = [];

    public bool $isBasicDirty = false;

    public function mount($liabilityId)
    {
        // Check if user has a valid session token
        if (! session('jwt_token')) {
            return redirect()->route('login');
        }

        $this->liability = Liability::find($liabilityId);

        if (! $this->liability) {
            session()->flash('error', 'Liability not found.');

            return redirect()->route('liabilities');
        }

        $this->loadLiabilityData();
    }

    protected function loadLiabilityData()
    {
        $this->name = $this->liability->name;
        $this->description = $this->liability->description;
        $this->title = $this->liability->title;
        $this->header = $this->liability->header ?? '';
        $this->todos = $this->liability->todos ?? [];
    }

    public function saveBasicDetails()
    {
        $this->validate([
            'name' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:3|max:200',
            'title' => 'required|string|min:3|max:255',
            'header' => 'nullable|string',
        ]);

        $this->liability->update([
            'name' => $this->name,
            'description' => $this->description,
            'title' => $this->title,
            'header' => $this->header,
            'todos' => $this->todos,
        ]);

        $this->isBasicDirty = false;
        session()->flash('success', 'Liability details updated successfully.');
        $this->dispatch('section-saved', ['section' => 'basic']);
    }

    public function backToList()
    {
        return redirect()->route('liabilities');
    }

    public function render()
    {
        return view('livewire.homepage.liabilities.liability-manage');
    }
}
