<div>
    <div class="kt-card kt-card-grid kt-card-div h-full min-w-full">
        <div class="kt-card-header flex justify-between items-center">
            <h3 class="kt-card-title">Liabilities</h3>
            <div class="flex gap-2 items-center">
                <div class="kt-input max-w-48">
                    <i class="ki-filled ki-magnifier"></i>
                    <input type="text" class="kt-input" placeholder="Search Liabilities" wire:model.live="search" />
                </div>
                <button class="kt-btn kt-btn-outline flex items-center justify-center" data-kt-modal-toggle="#liability_add_modal" title="Add Liability">
                    <i class="ki-filled ki-plus"></i>
                </button>
            </div>
        </div>
        <div class="kt-card-table">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table kt-table-border table-fixed w-full">
                    <thead>
                        <tr>
                            <th class="w-20 text-center">#</th>
                            <th class="">Name</th>
                            <th class="">Description</th>
                            <th class="text-center">Todos Count</th>
                            <th class="w-20 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
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
                                                <li>
                                                    <a class="kt-dropdown-menu-link" data-kt-dropdown-dismiss="true" onclick="confirmDelete('{{ $liability->id }}', '{{ addslashes($liability->name) }}')">
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
                                <td colspan="5" class="text-center py-8">
                                    <div class="flex flex-col items-center gap-2">
                                        <i class="ki-filled ki-file-down text-gray-400 text-3xl"></i>
                                        <span class="text-gray-500">No liabilities found</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-ktui-pagination :paginator="$this->liabilities" />

    <script>
        function confirmDelete(liabilityId, liabilityName) {
            if (confirm('Are you sure you want to delete "' + liabilityName + '"? This action cannot be undone.')) {
                @this.call('deleteLiability', liabilityId);
            }
        }
    </script>
</div>
