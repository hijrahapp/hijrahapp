<?php

namespace App\Livewire\Homepage\Interests;

use App\Models\Interest;
use Livewire\Component;

class InterestAddModal extends Component
{
    public string $name = '';

    public string $img_url = '';

    public string $error = '';

    public bool $isEditMode = false;

    public ?int $interestId = null;

    protected function rules()
    {
        return [
            'name' => 'required|string|min:2|max:255',
            'img_url' => 'required|string|url|max:500',
        ];
    }

    protected $listeners = [
        'reset-modal' => 'resetForm',
        'edit-interest' => 'editInterest',
    ];

    public function resetForm()
    {
        $this->name = '';
        $this->img_url = '';
        $this->error = '';
        $this->isEditMode = false;
        $this->interestId = null;
        $this->resetValidation();
    }

    public function editInterest($interestId)
    {
        $interest = Interest::find($interestId);
        if ($interest) {
            $this->interestId = $interest->id;
            $this->name = $interest->name;
            $this->img_url = $interest->img_url ?? '';
            $this->isEditMode = true;
        }
    }

    public function save()
    {
        try {
            $this->error = '';
            $this->validate();

            $data = [
                'name' => $this->name,
                'img_url' => $this->img_url ?: null,
                'active' => false,
            ];

            if ($this->isEditMode && $this->interestId) {
                $interest = Interest::find($this->interestId);
                if ($interest) {
                    // Keep the original active status when editing
                    unset($data['active']);
                    $interest->update($data);
                }
            } else {
                Interest::create($data);
            }

            $this->dispatch('refreshTable');
            $this->closeModal();
            $this->dispatch('show-toast', type: 'success', message: $this->isEditMode ? 'Interest updated successfully.' : 'Interest created successfully.');
        } catch (\Exception $e) {
            $this->error = 'Failed to save interest: '.$e->getMessage();
        }
    }

    public function saveAndAddNew()
    {
        try {
            $this->error = '';
            $this->validate();

            $data = [
                'name' => $this->name,
                'img_url' => $this->img_url ?: null,
                'active' => false,
            ];

            Interest::create($data);

            $this->dispatch('refreshTable');
            $this->dispatch('show-toast', type: 'success', message: 'Interest created successfully.');

            // Reset form but keep modal open
            $this->name = '';
            $this->img_url = '';
            $this->resetValidation();
        } catch (\Exception $e) {
            $this->error = 'Failed to save interest: '.$e->getMessage();
        }
    }

    public function closeModal()
    {
        $this->dispatch('click');
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.homepage.interests.interest-add-modal');
    }
}
