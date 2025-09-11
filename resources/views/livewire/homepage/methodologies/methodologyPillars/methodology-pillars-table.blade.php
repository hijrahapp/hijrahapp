<div>
    <div class="kt-card kt-card-grid kt-card-div h-full min-w-full">
        <div class="kt-card-header flex justify-between items-center">
            <h3 class="kt-card-title">Pillars</h3>
            <div class="flex gap-2 items-center">
                <div class="kt-input max-w-48">
                    <i class="ki-filled ki-magnifier"></i>
                    <input type="text" class="kt-input" placeholder="Search Pillars" wire:model.live="search" />
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
                <button class="kt-btn kt-btn-outline flex items-center justify-center" title="Add Pillar" wire:click="openAddModal">
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
                            <th class="">Dependencies</th>
                            @if(($methodology->type ?? null) === 'twoSection')
                                <th class="w-28 text-center">Section</th>
                            @endif
                            <th class="w-20 text-center">Weight</th>
                            <th class="w-20 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pillars as $index => $pillar)
                            <tr>
                                <td class="text-center">{{ $pillars->firstItem() + $index }}</td>
                                <td>{{ $pillar->name }}</td>
                                <td>
                                    @php
                                        $dependencyNames = $this->getDependencyNames($pillar->id);
                                    @endphp
                                    @if(count($dependencyNames) > 0)
                                        <div class="flex flex-wrap gap-1 items-center">
                                            @foreach($dependencyNames as $depName)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary border border-primary/20">
                                                    {{ $depName }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">No dependencies</span>
                                    @endif
                                </td>
                                @if(($methodology->type ?? null) === 'twoSection')
                                    @php
                                        $section = \DB::table('methodology_pillar')
                                            ->where('methodology_id', $this->methodologyId)
                                            ->where('pillar_id', $pillar->id)
                                            ->value('section');
                                    @endphp
                                    <td class="text-center">{{ $section === 'second' ? '2' : '1' }}</td>
                                @endif
                                <td class="text-center">{{ $pillar->mp_weight !== null ? (int) $pillar->mp_weight : '-' }}%</td>
                                <td class="text-center" wire:ignore>
                                    <div data-kt-dropdown="true" data-kt-dropdown-trigger="click">
                                        <button class="kt-btn kt-btn-outline" data-kt-dropdown-toggle="true">
                                            <i class="ki-filled ki-dots-horizontal text-secondary-foreground"></i>
                                        </button>
                                        <div class="kt-dropdown-menu" data-kt-dropdown-menu="true">
                                            <ul class="kt-dropdown-menu-sub">
                                                <li>
                                                    <a class="kt-dropdown-menu-link" data-kt-dropdown-dismiss="true" wire:click="openEditModal({{ $pillar->id }})">
                                                        <i class="ki-filled ki-pencil"></i>
                                                        Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="kt-dropdown-menu-link text-danger" data-kt-dropdown-dismiss="true" wire:click="openDeleteModal({{ $pillar->id }})">
                                                        <i class="ki-filled ki-trash"></i>
                                                        Remove
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No Pillars found.</td>
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
                    Showing {{ $pillars->firstItem() ?? 0 }} to {{ $pillars->lastItem() ?? 0 }} of {{ $pillars->total() ?? 0 }} Pillars
                </span>
            </div>
        </div>
    </div>

    <x-ktui-pagination :paginator="$pillars" />
</div>


