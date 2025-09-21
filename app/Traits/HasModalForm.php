<?php

namespace App\Traits;

trait HasModalForm
{
    public bool $isEditMode = false;
    public ?string $error = null;
    public ?int $editingId = null;

    // Modal state management
    public function openModal(): void
    {
        $this->resetModalState();
        $this->isEditMode = false;
        $this->dispatch('openModal');
    }

    public function openEditModal(int $id): void
    {
        $this->resetModalState();
        $this->isEditMode = true;
        $this->editingId = $id;
        $this->loadForEdit($id);
        $this->dispatch('openModal');
    }

    public function closeModal(): void
    {
        $this->resetModalState();
        $this->dispatch('closeModal');
    }

    // Reset all modal-related properties
    protected function resetModalState(): void
    {
        $this->error = null;
        $this->editingId = null;
        $this->resetErrorBag();
        $this->resetFormFields();
    }

    // Override in implementing classes to define form fields
    protected function resetFormFields(): void
    {
        // Default implementation - override in implementing classes
    }

    // Override in implementing classes to load data for editing
    protected function loadForEdit(int $id): void
    {
        // Default implementation - override in implementing classes
    }

    // Common validation patterns
    protected function validateRequired(array $fields, array $rules = []): void
    {
        $defaultRules = [];

        foreach ($fields as $field => $label) {
            $defaultRules[$field] = ['required', 'string', 'max:255'];
        }

        $finalRules = array_merge($defaultRules, $rules);

        $this->validate($finalRules, [], $fields);
    }

    // Common success/error handling
    protected function handleSuccess(string $message, string $event = 'refreshTable'): void
    {
        $this->dispatch('show-toast', type: 'success', message: $message);
        $this->dispatch($event);
        $this->closeModal();
    }

    protected function handleError(string $message): void
    {
        $this->error = $message;
    }

    protected function handleValidationError(\Exception $e): void
    {
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            $this->setErrorBag($e->validator->errors());
        } else {
            $this->handleError($e->getMessage());
        }
    }

    // Form submission wrapper with error handling
    protected function submitForm(callable $action, string $successMessage): void
    {
        try {
            $this->resetErrorBag();
            $this->error = null;

            $result = $action();

            $this->handleSuccess($successMessage);

            return $result;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->handleValidationError($e);
        } catch (\Exception $e) {
            $this->handleError('An error occurred: ' . $e->getMessage());
        }
    }

    // Common form field helpers
    protected function getFormField(string $field, $default = null)
    {
        return $this->{$field} ?? $default;
    }

    protected function setFormField(string $field, $value): void
    {
        $this->{$field} = $value;
    }

    protected function getFormFields(array $fields): array
    {
        $data = [];
        foreach ($fields as $field) {
            $data[$field] = $this->getFormField($field);
        }
        return $data;
    }

    protected function setFormFields(array $data): void
    {
        foreach ($data as $field => $value) {
            $this->setFormField($field, $value);
        }
    }

    // Common model operations
    protected function createModel(string $modelClass, array $data)
    {
        return $modelClass::create($data);
    }

    protected function updateModel(string $modelClass, int $id, array $data)
    {
        $model = $modelClass::findOrFail($id);
        $model->update($data);
        return $model;
    }

    protected function saveModel(string $modelClass, array $data, array $fields)
    {
        $formData = $this->getFormFields($fields);
        $finalData = array_merge($data, $formData);

        if ($this->isEditMode && $this->editingId) {
            return $this->updateModel($modelClass, $this->editingId, $finalData);
        } else {
            return $this->createModel($modelClass, $finalData);
        }
    }

    // Modal configuration helpers
    protected function getModalConfig(): array
    {
        return [
            'isEditMode' => $this->isEditMode,
            'error' => $this->error,
            'editingId' => $this->editingId,
        ];
    }

    // Form button states
    protected function isSubmitDisabled(): bool
    {
        return false; // Override in implementing classes
    }

    protected function getSubmitText(): string
    {
        return $this->isEditMode ? 'Update' : 'Create';
    }

    // Event dispatchers
    protected function dispatchTableRefresh(): void
    {
        $this->dispatch('refreshTable');
    }

    protected function dispatchModalClose(): void
    {
        $this->dispatch('modal-closed');
    }

    protected function dispatchFormSaved(array $data = []): void
    {
        $this->dispatch('form-saved', $data);
    }
}