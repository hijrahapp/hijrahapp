<x-table-layout
    title="Liabilities"
    searchPlaceholder="Search Liabilities"
    :paginator="$this->liabilities"
    addButtonModal="#liability_add_modal"
    addButtonText="Add Liability"
    emptyMessage="No liabilities found"
    counterText="liabilities"
>
    <x-slot name="tableHeader">
        <tr>
            <th class="w-20 text-center">#</th>
            <th class="">Name</th>
            <th class="">Description</th>
            <th class="text-center">To-Do Items Count</th>
            <th class="text-center">Status</th>
            <th class="w-20 text-center">Actions</th>
        </tr>
    </x-slot>

    <x-slot name="tableBody">
        @forelse($this->liabilities as $index => $liability)
            <tr>
                <td class="text-center">{{ ($this->liabilities->currentPage() - 1) * $this->liabilities->perPage() + $index + 1 }}</td>
                <td>
                    <div class="flex flex-col">
                        <div class="text-sm font-medium text-gray-900">{{ $liability->name }}</div>
                    </div>
                </td>
                <td>
                    <div class="text-sm text-gray-600 max-w-xs truncate">{{ $liability->description }}</div>
                </td>
                <td class="text-center">
                    <span class="kt-badge kt-badge-light-primary">{{ count($liability->todos ?? []) }}</span>
                </td>
                <td class="text-center">
                    @php $statusButton = $this->getStatusButton($liability) @endphp
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
                                    <a class="kt-dropdown-menu-link" data-kt-dropdown-dismiss="true" wire:click="manageLiability({{ $liability->id }})">
                                        <i class="ki-filled ki-setting-2"></i>
                                        Manage
                                    </a>
                                </li>

                                <li class="kt-dropdown-menu-separator"></li>

                                <li>
                                    <a href="#" class="kt-dropdown-menu-link" data-kt-dropdown-dismiss="true" wire:click="viewUsers({{ $liability->id }})">
                                        <i class="ki-filled ki-users"></i>
                                        View Users
                                    </a>
                                </li>

                                <li class="kt-dropdown-menu-separator"></li>

                                <li>
                                    <a class="kt-dropdown-menu-link text-danger" data-kt-dropdown-dismiss="true" wire:click="openDeleteLiabilityModal('{{ $liability->id }}')">
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
                <td colspan="6" class="text-center py-8">
                    <div class="flex flex-col items-center gap-2">
                        <i class="ki-filled ki-file-down text-gray-400 text-3xl"></i>
                        <span class="text-gray-500">No liabilities found</span>
                    </div>
                </td>
            </tr>
        @endforelse
    </x-slot>
</x-table-layout>
