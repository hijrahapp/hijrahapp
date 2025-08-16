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
            <button class="kt-btn kt-btn-outline flex items-center justify-center" data-kt-modal-toggle="#module_add_modal" title="Add Module">
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
                        <th class="">Definition</th>
                        <th class="">Tags</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($modules as $index => $module)
                        <tr>
                            <td class="text-center">{{ $modules->firstItem() + $index }}</td>
                            <td>{{ $module->name }}</td>
                            <td>
                                <div class="max-w-xs truncate" title="{{ $module->description }}">
                                    {{ Str::limit($module->description, 50) }}
                                </div>
                            </td>
                            <td>
                                <div class="max-w-xs truncate" title="{{ $module->definition }}">
                                    {{ Str::limit($module->definition, 50) }}
                                </div>
                            </td>
                            <td>
                                @php
                                    $tagData = $this->getTagTitles($module->tags);
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
                                @if($module->active)
                                    <button class="kt-btn kt-btn-outline kt-btn-sm kt-btn-destructive" x-on:click="$wire.call('openModuleStatusModal', {{ Js::from(['id' => $module->id, 'active' => false]) }})" title="Deactivate Module">
                                        Deactivate
                                    </button>
                                @else
                                    <button class="kt-btn kt-btn-outline kt-btn-sm kt-btn-primary" x-on:click="$wire.call('openModuleStatusModal', {{ Js::from(['id' => $module->id, 'active' => true]) }})" title="Activate Module">
                                        Activate
                                    </button>
                                @endif
                            </td>
                            <td class="text-center flex gap-2 justify-center">
                                <button
                                    class="kt-btn kt-btn-outline flex items-center justify-center"
                                    wire:click="editModule({{ $module->id }})"
                                    title="Edit Module">
                                    <i class="ki-filled ki-pencil text-secondary-foreground"></i>
                                </button>
                                <button
                                    class="kt-btn kt-btn-outline flex items-center justify-center"
                                    wire:click="openDeleteModuleModal({{ Js::from(['id' => $module->id]) }})"
                                    title="Delete Module">
                                    <i class="ki-filled ki-trash text-destructive"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">No Modules found.</td>
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
                Showing {{ $modules->firstItem() ?? 0 }} to {{ $modules->lastItem() ?? 0 }} of {{ $modules->total() ?? 0 }} Modules
            </span>
        </div>
    </div>
</div>

    {{-- Pagination outside the table card --}}
    <x-ktui-pagination :paginator="$modules" />
</div>


