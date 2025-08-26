<div>
    <div class="kt-card kt-card-grid kt-card-div h-full min-w-full">
        <div class="kt-card-header flex justify-between items-center">
            <h3 class="kt-card-title">Modules</h3>
            <div class="flex gap-2 items-center">
                <div class="kt-input max-w-48">
                    <i class="ki-filled ki-magnifier"></i>
                    <input type="text" class="kt-input" placeholder="Search Modules" wire:model.live="search" />
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
                <button class="kt-btn kt-btn-outline flex items-center justify-center" title="Add Module" wire:click="openAddModal">
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
                            <th class="">Report</th>
                            @if(\DB::table('methodology_pillar')->where('methodology_id', $methodology->id)->exists())
                                <th class="">Pillar</th>
                            @endif
                            <th class="">Dependencies</th>
                            <th class="w-28 text-center">Number of Questions</th>
                            <th class="w-20 text-center">Weight</th>
                            <th class="w-20 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($modules as $index => $module)
                            <tr>
                                <td class="text-center">{{ $modules->firstItem() + $index }}</td>
                                <td class="">{{ $module->name }}</td>
                                <td>
                                    <div class="max-w-xs truncate" title="{{ $module->mm_reports }}">
                                        {{ Str::limit($module->mm_reports, 50) }}
                                    </div>
                                </td>
                                @if(\DB::table('methodology_pillar')->where('methodology_id', $methodology->id)->exists())
                                    <td>{{ $module->pillar_name ?? '-' }}</td>
                                @endif
                                <td>
                                    @php
                                        $dependencyNames = $this->getDependencyNames($module->id);
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
                                <td class="text-center">{{ $module->questions_count ?? 0 }}</td>
                                <td class="text-center">{{ $module->mm_weight !== null ? (int) $module->mm_weight : '-' }}%</td>
                                <td class="text-center" wire:ignore>
                                    <div data-kt-dropdown="true" data-kt-dropdown-trigger="click">
                                        <button class="kt-btn kt-btn-outline" data-kt-dropdown-toggle="true">
                                            <i class="ki-filled ki-dots-horizontal text-secondary-foreground"></i>
                                        </button>
                                        <div class="kt-dropdown-menu w-52" data-kt-dropdown-menu="true">
                                            <ul class="kt-dropdown-menu-sub">
                                                <li>
                                                    <a class="kt-dropdown-menu-link" data-kt-dropdown-dismiss="true" wire:click="manageQuestions({{ $module->id }})">
                                                        <i class="ki-filled ki-question-2"></i>
                                                        Manage Questions
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="kt-dropdown-menu-link" data-kt-dropdown-dismiss="true" wire:click="openEditModal({{ $module->id }})">
                                                        <i class="ki-filled ki-pencil"></i>
                                                        Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="kt-dropdown-menu-link text-danger" data-kt-dropdown-dismiss="true" wire:click="openDeleteModal({{ $module->id }})">
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
                                <td 
                                @if(\DB::table('methodology_pillar')->where('methodology_id', $methodology->id)->exists()) 
                                colspan="8" 
                                @else 
                                colspan="7" 
                                @endif 
                                class="text-center py-4">No Modules found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="kt-card-footer flex-col justify-center gap-5 text-sm font-medium text-secondary-foreground md:flex-row md:justify-between">
            <div class="order-2 flex items-center gap-2 md:order-1"></div>
            <div class="order-1 flex items-center gap-4 md:order-2">
                <span>
                    Showing {{ $modules->firstItem() ?? 0 }} to {{ $modules->lastItem() ?? 0 }} of {{ $modules->total() ?? 0 }} Modules
                </span>
            </div>
        </div>
    </div>

    {{-- Pagination outside the table card --}}
    <x-ktui-pagination :paginator="$modules" />
</div>


