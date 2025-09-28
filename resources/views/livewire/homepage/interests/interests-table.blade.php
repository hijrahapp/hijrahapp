<div>
    <div class="kt-card kt-card-grid kt-card-div h-full min-w-full">
        <div class="kt-card-header flex justify-between items-center">
            <h3 class="kt-card-title">Interests</h3>
            <div class="flex gap-2 items-center">
                <div class="kt-input max-w-48">
                    <i class="ki-filled ki-magnifier"></i>
                    <input type="text" class="kt-input" placeholder="Search Interests" wire:model.live="search" />
                </div>
                <button class="kt-btn kt-btn-outline flex items-center justify-center" data-kt-modal-toggle="#interest_add_modal" title="Add Interest">
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
                            <th class="w-24 text-center">Image</th>
                            <th class="">Name</th>
                            <th class="text-center">Status</th>
                            <th class="w-20 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->interests as $index => $interest)
                            <tr>
                                <td class="text-center">{{ ($this->interests->currentPage() - 1) * $this->interests->perPage() + $index + 1 }}</td>
                                <td class="text-center">
                                    @if($interest->img_url)
                                        <div class="flex justify-center">
                                            <img src="{{ $interest->img_url }}" alt="{{ $interest->name }}" class="w-12 h-12 rounded-lg object-cover border border-gray-200">
                                        </div>
                                    @else
                                        <div class="flex justify-center">
                                            <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                                                <i class="ki-filled ki-picture text-gray-400"></i>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex flex-col">
                                        <div class="text-sm font-medium text-gray-900">{{ $interest->name }}</div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($interest->active)
                                        <button class="kt-btn kt-btn-outline kt-btn-sm kt-btn-destructive" x-on:click="$wire.call('openInterestStatusModal', {{ Js::from(['id' => $interest->id, 'active' => false]) }})" title="Deactivate Interest">
                                            Deactivate
                                        </button>
                                    @else
                                        <button class="kt-btn kt-btn-outline kt-btn-sm kt-btn-primary" x-on:click="$wire.call('openInterestStatusModal', {{ Js::from(['id' => $interest->id, 'active' => true]) }})" title="Activate Interest">
                                            Activate
                                        </button>
                                    @endif
                                </td>
                                <td class="text-center" wire:ignore>
                                    <div data-kt-dropdown="true" data-kt-dropdown-trigger="click">
                                        <button class="kt-btn kt-btn-outline" data-kt-dropdown-toggle="true">
                                            <i class="ki-filled ki-dots-horizontal text-secondary-foreground"></i>
                                        </button>
                                        <div class="kt-dropdown-menu" data-kt-dropdown-menu="true">
                                            <ul class="kt-dropdown-menu-sub">
                                                <li>
                                                    <a class="kt-dropdown-menu-link" data-kt-dropdown-dismiss="true" data-kt-modal-toggle="#interest_add_modal" wire:click="editInterest({{ $interest->id }})">
                                                        <i class="ki-filled ki-setting-2"></i>
                                                        Edit
                                                    </a>
                                                </li>

                                                <li class="kt-dropdown-menu-separator"></li>

                                                <li>
                                                    <a class="kt-dropdown-menu-link text-danger" data-kt-dropdown-dismiss="true" wire:click="openDeleteInterestModal('{{ $interest->id }}')">
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
                                        <i class="ki-filled ki-star text-gray-400 text-3xl"></i>
                                        <span class="text-gray-500">No interests found</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="kt-card-footer flex-col justify-center gap-5 text-sm font-medium text-secondary-foreground md:flex-row md:justify-between">
            <div class="order-2 flex items-center gap-2 md:order-1">
            </div>
            <div class="order-1 flex items-center gap-4 md:order-2">
                <span>
                    Showing {{ $this->interests->firstItem() ?? 0 }} to {{ $this->interests->lastItem() ?? 0 }} of {{ $this->interests->total() ?? 0 }} Interests
                </span>
            </div>
        </div>
    </div>

    <x-ktui-pagination :paginator="$this->interests" />
</div>
