<?php

namespace App\Traits;

use Livewire\Attributes\Computed;

trait HasDataTable
{
    // These properties should be defined in implementing classes:
    // public string $search = '';
    // public int $perPage = 10;
    // protected string $modelClass;
    // protected string $entityName;
    // protected array $searchFields = ['name'];
    // protected string $defaultOrderBy = 'created_at';
    // protected string $defaultOrderDirection = 'desc';

    // Override in implementing class if needed
    protected function getBaseQuery()
    {
        $query = $this->modelClass::query();

        // Apply search
        if (!empty($this->search)) {
            $searchFields = $this->searchFields ?? ['name'];
            $query->where(function ($q) use ($searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', '%' . $this->search . '%');
                }
            });
        }

        $orderBy = $this->defaultOrderBy ?? 'created_at';
        $orderDirection = $this->defaultOrderDirection ?? 'desc';

        return $query->orderBy($orderBy, $orderDirection);
    }

    // Common action methods
    public function openStatusModal(array $request): void
    {
        $model = $this->modelClass::findOrFail($request['id']);

        if ($request['active']) {
            $title = __("messages.activate_{$this->entityName}_title");
            $message = __("messages.activate_{$this->entityName}_message");
            $action = __('messages.activate_action');
            $note = null;
        } else {
            $title = __("messages.deactivate_{$this->entityName}_title");
            $message = __("messages.deactivate_{$this->entityName}_message");
            $action = __('messages.deactivate_action');
            $note = __("messages.deactivate_{$this->entityName}_note");
        }

        $modal = [
            'title' => $title,
            'message' => $message,
            'note' => $note,
            'action' => $action,
            'callback' => 'changeStatus',
            'object' => $request,
        ];

        $this->dispatch('openConfirmationModal', $modal);
    }

    public function changeStatus(array $request): void
    {
        $model = $this->modelClass::findOrFail($request['id']);
        $model->active = $request['active'];
        $model->save();

        $this->reloadTable();

        $message = $request['active']
            ? ucfirst($this->entityName) . ' activated successfully!'
            : ucfirst($this->entityName) . ' deactivated successfully!';

        $this->dispatch('show-toast', type: 'success', message: $message);
    }

    public function openDeleteModal($id): void
    {
        $modal = [
            'title' => __("messages.delete_{$this->entityName}_title"),
            'message' => __("messages.delete_{$this->entityName}_message"),
            'note' => __("messages.delete_{$this->entityName}_note"),
            'action' => __('messages.delete_action'),
            'callback' => 'deleteRecord',
            'object' => $id,
        ];

        $this->dispatch('openConfirmationModal', $modal);
    }

    public function deleteRecord($id): void
    {
        try {
            $model = $this->modelClass::find($id);
            if ($model) {
                $model->delete();
                $this->dispatch('show-toast', type: 'success', message: ucfirst($this->entityName) . ' deleted successfully.');
                $this->reloadTable();
            }
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Failed to delete ' . $this->entityName . ': ' . $e->getMessage());
        }
    }

    // Common dropdown actions - override in implementing class as needed
    public function getDropdownActions(object $model): array
    {
        return [
            'manage' => [
                'label' => 'Manage',
                'icon' => 'ki-filled ki-setting-2',
                'action' => 'manage' . ucfirst($this->entityName),
                'params' => [$model->id]
            ],
            'separator1' => ['type' => 'separator'],
            'viewUsers' => [
                'label' => 'View Users',
                'icon' => 'ki-filled ki-users',
                'action' => 'viewUsers',
                'params' => [$model->id]
            ],
            'separator2' => ['type' => 'separator'],
            'delete' => [
                'label' => 'Delete',
                'icon' => 'ki-filled ki-trash',
                'class' => 'text-danger',
                'action' => 'openDeleteModal',
                'params' => [$model->id]
            ]
        ];
    }

    // Status button helper
    public function getStatusButton(object $model): array
    {
        if ($model->active) {
            return [
                'class' => 'kt-btn kt-btn-outline kt-btn-sm kt-btn-destructive',
                'action' => 'openStatusModal',
                'params' => ['id' => $model->id, 'active' => false],
                'text' => 'Deactivate',
                'title' => 'Deactivate ' . ucfirst($this->entityName)
            ];
        } else {
            return [
                'class' => 'kt-btn kt-btn-outline kt-btn-sm kt-btn-primary',
                'action' => 'openStatusModal',
                'params' => ['id' => $model->id, 'active' => true],
                'text' => 'Activate',
                'title' => 'Activate ' . ucfirst($this->entityName)
            ];
        }
    }

    // Helper method for row numbering (use in views)
    // {{ ($paginator->currentPage() - 1) * $paginator->perPage() + $index + 1 }}
}