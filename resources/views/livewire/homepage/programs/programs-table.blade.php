<x-table-layout
    title="Programs"
    searchPlaceholder="Search Programs"
    :paginator="$this->programs"
    addButtonModal="#program_add_modal"
    addButtonText="Add Program"
    emptyMessage="No programs found"
    counterText="programs"
    :filters="[
        [
            'type' => 'select',
            'model' => 'statusFilter',
            'placeholder' => 'All Status',
            'options' => [
                'active' => 'Active',
                'inactive' => 'Inactive'
            ]
        ]
    ]"
>
    <x-slot name="tableHeader">
        <tr>
            <th class="w-20 text-center">#</th>
            <th class="">Name</th>
            <th class="">Description</th>
            <th class="text-center">Steps Count</th>
            <th class="text-center">Status</th>
            <th class="w-20 text-center">Actions</th>
        </tr>
    </x-slot>

    <x-slot name="tableBody">
        @forelse($this->programs as $index => $program)
            <tr>
                <td class="text-center">{{ ($this->programs->currentPage() - 1) * $this->programs->perPage() + $index + 1 }}</td>
                <td>
                    <div class="flex flex-col">
                        <div class="text-sm font-medium text-gray-900">{{ $program->name }}</div>
                    </div>
                </td>
                <td>
                    <div class="text-sm text-gray-600 max-w-xs truncate">{{ $program->description }}</div>
                </td>
                <td class="text-center">
                    <span class="kt-badge kt-badge-light-primary">{{ $program->steps_list_count ?? 0 }}</span>
                </td>
                <td class="text-center">
                    @php $statusButton = $this->getStatusButton($program) @endphp
                    <button class="{{ $statusButton['class'] }}"
                            x-on:click="$wire.call('{{ $statusButton['action'] }}', {{ Js::from($statusButton['params']) }})"
                            title="{{ $statusButton['title'] }}">
                        {{ $statusButton['text'] }}
                    </button>
                </td>
                <td class="text-center" wire:ignore>
                    <div data-kt-dropdown="true" data-kt-dropdown-trigger="click">
                        <button class="kt-btn kt-btn-outline" data-kt-dropdown-toggle="true">
                            <i class="ki-filled ki-dots-horizontal text-secondary-foreground"></i>
                        </button>
                        <div class="kt-dropdown-menu" data-kt-dropdown-menu="true">
                            <ul class="kt-dropdown-menu-sub">
                                <li>
                                    <a class="kt-dropdown-menu-link" data-kt-dropdown-dismiss="true" wire:click="manageProgram({{ $program->id }})">
                                        <i class="ki-filled ki-setting-2"></i>
                                        Manage
                                    </a>
                                </li>

                                <li class="kt-dropdown-menu-separator"></li>

                                <li>
                                    <a href="#" class="kt-dropdown-menu-link" data-kt-dropdown-dismiss="true" wire:click="viewUsers({{ $program->id }})">
                                        <i class="ki-filled ki-users"></i>
                                        View Users
                                    </a>
                                </li>

                                <li class="kt-dropdown-menu-separator"></li>

                                <li>
                                    <a class="kt-dropdown-menu-link text-danger" data-kt-dropdown-dismiss="true" wire:click="openDeleteProgramModal('{{ $program->id }}')">
                                        <i class="ki-filled ki-trash"></i>
                                        Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-4">No programs found</td>
            </tr>
        @endforelse
    </x-slot>
</x-table-layout>
