<div>
    <div class="kt-card kt-card-grid kt-card-div h-full min-w-full">
        <div class="kt-card-header flex justify-between items-center">
            <h3 class="kt-card-title">Methodologies</h3>
            <div class="flex gap-2 items-center">
                <div class="kt-input max-w-48">
                    <i class="ki-filled ki-magnifier"></i>
                    <input type="text" class="kt-input" placeholder="Search Methodologies" wire:model.live="search" />
                </div>
                <div class="relative max-w-64 min-w-56">
                    <div class="kt-input">
                        <i class="ki-filled ki-filter"></i>
                        <input type="text" class="kt-input" placeholder="Filter by tag" wire:model.live="tagSearch" />
                        @if($tagFilter)
                            <button type="button" class="absolute end-2 top-1/2 -translate-y-1/2 text-muted-foreground" title="Clear" wire:click="clearTagFilter">
                                <i class="ki-filled ki-cross"></i>
                            </button>
                        @endif
                    </div>
                    @if($showTagSuggestions && count($tagSuggestions) > 0)
                        <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                            @foreach($tagSuggestions as $suggestion)
                                <button type="button" class="w-full text-left px-4 py-2 hover:bg-gray-100" wire:click="selectTagFilter({{ $suggestion['id'] }}, '{{ $suggestion['title'] }}')">
                                    {{ $suggestion['title'] }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
                <button class="kt-btn kt-btn-outline flex items-center justify-center" data-kt-modal-toggle="#methodology_add_modal" title="Add Methodology">
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
                            <th class="w-20 text-center">Image</th>
                            <th class="">Name</th>
                            <th class="text-center">Type</th>
                            <th class="">Tags</th>
                            <th class="text-center">Status</th>
                            <th class="w-20 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($methodologies as $index => $methodology)
                            <tr>
                                <td class="text-center">{{ $methodologies->firstItem() + $index }}</td>
                                <td class="text-center">
                                    @if($methodology->img_url)
                                        <div class="flex justify-center">
                                            <img src="{{ $methodology->img_url }}" alt="{{ $methodology->name }}" class="w-8 h-8 rounded object-cover">
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">No image</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $methodology->name }}</span>
                                        @if($methodology->description)
                                            <span class="text-sm text-gray-500 truncate max-w-xs" title="{{ $methodology->description }}">
                                                {{ Str::limit($methodology->description, 50) }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        @if($methodology->type == 'simple')
                                        bg-green-50 text-green-600
                                        @elseif($methodology->type == 'complex')
                                        bg-blue-50 text-blue-600
                                        @else
                                        bg-violet-50 text-violet-600
                                        @endif">
                                        {{ __('lookups.'.$methodology->type) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $tagData = $this->getTagTitles($methodology->tags);
                                    @endphp
                                    @if(count($tagData['tags']) > 0)
                                        <div class="flex flex-wrap gap-1 items-center">
                                            @foreach($tagData['tags'] as $tagTitle)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary border border-primary/20">
                                                    {{ $tagTitle }}
                                                </span>
                                            @endforeach
                                            @if($tagData['hasMore'])
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200" title="+{{ $tagData['remainingCount'] }} more tags">
                                                    +{{ $tagData['remainingCount'] }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">No tags</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($methodology->active)
                                        <button class="kt-btn kt-btn-outline kt-btn-sm kt-btn-destructive" x-on:click="$wire.call('openMethodologyStatusModal', {{ Js::from(['id' => $methodology->id, 'active' => false]) }})" title="Deactivate Methodology">
                                            Deactivate
                                        </button>
                                    @else
                                        <button class="kt-btn kt-btn-outline kt-btn-sm kt-btn-primary" x-on:click="$wire.call('openMethodologyStatusModal', {{ Js::from(['id' => $methodology->id, 'active' => true]) }})" title="Activate Methodology">
                                            Activate
                                        </button>
                                    @endif
                                </td>
                                <td class="text-center" wire:ignore>
                                    <div data-kt-dropdown="true" data-kt-dropdown-trigger="click">
                                        <button class="kt-btn kt-btn-outline" data-kt-dropdown-toggle="true">
                                            <i class="ki-filled ki-dots-horizontal text-secondary-foreground"></i>
                                        </button>
                                        <div class="kt-dropdown-menu w-52" data-kt-dropdown-menu="true">
                                            <ul class="kt-dropdown-menu-sub">
                                                <li>
                                                    <a href="{{ route('methodology.manage', ['methodologyId' => $methodology->id]) }}" class="kt-dropdown-menu-link" data-kt-dropdown-dismiss="true">
                                                        <i class="ki-filled ki-setting-2"></i>
                                                        Manage
                                                    </a>
                                                </li>

                                                <li class="kt-dropdown-menu-separator"></li>

                                                <li>
                                                    <a href="#" class="kt-dropdown-menu-link" data-kt-dropdown-dismiss="true" wire:click="viewUsers({{ $methodology->id }})">
                                                        <i class="ki-filled ki-users"></i>
                                                        View Users
                                                    </a>
                                                </li>

                                                <li class="kt-dropdown-menu-separator"></li>

                                                <li>
                                                    <a href="#" class="kt-dropdown-menu-link text-danger" data-kt-dropdown-dismiss="true" wire:click="openDeleteMethodologyModal({{ Js::from(['id' => $methodology->id]) }})">
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
                                <td colspan="7" class="text-center py-4">No Methodologies found.</td>
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
                    Showing {{ $methodologies->firstItem() ?? 0 }} to {{ $methodologies->lastItem() ?? 0 }} of {{ $methodologies->total() ?? 0 }} Methodologies
                </span>
            </div>
        </div>
    </div>

    {{-- Pagination outside the table card --}}
    <x-ktui-pagination :paginator="$methodologies" />
</div>
