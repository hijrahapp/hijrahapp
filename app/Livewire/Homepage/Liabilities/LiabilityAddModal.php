<?php

namespace App\Livewire\Homepage\Liabilities;

use App\Models\Liability;
use Livewire\Component;

class LiabilityAddModal extends Component
{
    public string $name = '';

    public string $description = '';

    public string $title = '';

    public string $header = '';

    public array $todos = [];

    public string $error = '';

    public bool $isEditMode = false;

    public ?int $liabilityId = null;

    protected function rules()
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:3|max:200',
            'title' => 'required|string|min:3|max:255',
            'header' => 'nullable|string',
            'todos' => 'array',
            'todos.*' => 'string|max:500',
        ];
    }

    protected $listeners = [
        'reset-modal' => 'resetForm',
        'edit-liability' => 'editLiability',
    ];

    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->title = '';
        $this->header = '';
        $this->todos = [];
        $this->error = '';
        $this->isEditMode = false;
        $this->liabilityId = null;
        $this->resetValidation();
    }

    public function editLiability($liabilityId)
    {
        $liability = Liability::find($liabilityId);
        if ($liability) {
            $this->liabilityId = $liability->id;
            $this->name = $liability->name;
            $this->description = $liability->description;
            $this->title = $liability->title;
            $this->header = $liability->header ?? '';
            $this->todos = $liability->todos ?? [];
            $this->isEditMode = true;
        }
    }

    public function addTodo()
    {
        $this->todos[] = '';
    }

    public function removeTodo($index)
    {
        unset($this->todos[$index]);
        $this->todos = array_values($this->todos);
    }

    public function save()
    {
        try {
            $this->error = '';
            $this->validate();

            // Filter out empty todos
            $todos = array_filter($this->todos, fn ($todo) => ! empty(trim($todo)));

            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'title' => $this->title,
                'header' => $this->header,
                'todos' => array_values($todos),
            ];

            if ($this->isEditMode && $this->liabilityId) {
                $liability = Liability::find($this->liabilityId);
                if ($liability) {
                    $liability->update($data);
                    $this->dispatch('refreshTable');
                    session()->flash('success', 'Liability updated successfully.');
                }
            } else {
                Liability::create($data);
                $this->dispatch('refreshTable');
                session()->flash('success', 'Liability created successfully.');
            }

            $this->resetForm();
        } catch (\Exception $e) {
            $this->error = 'Failed to save liability: '.$e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.homepage.liabilities.liability-add-modal');
    }
}
