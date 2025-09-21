{{-- Generic Table Layout Component --}}
@props([
    'title',
    'searchPlaceholder' => 'Search...',
    'searchModel' => 'search',
    'paginator',
    'showAddButton' => true,
    'addButtonText' => 'Add',
    'addButtonModal' => null,
    'addButtonAction' => null,
    'filters' => [],
    'emptyMessage' => 'No items found',
    'showCounter' => true,
    'counterText' => 'items'
])

<div>
    <div class="kt-card kt-card-grid kt-card-div h-full min-w-full">
        {{-- Header --}}
        <div class="kt-card-header flex justify-between items-center">
            <h3 class="kt-card-title">{{ $title }}</h3>
            <div class="flex gap-2 items-center">
                {{-- Search Input --}}
                <div class="kt-input max-w-48">
                    <i class="ki-filled ki-magnifier"></i>
                    <input type="text" class="kt-input" placeholder="{{ $searchPlaceholder }}" wire:model.live="{{ $searchModel }}" />
                </div>

                {{-- Additional Filters Slot --}}
                @if(count($filters) > 0)
                    @foreach($filters as $filter)
                        <div class="relative">
                            <div class="kt-input">
                                <i class="ki-filled ki-filter"></i>
                                @if($filter['type'] === 'select')
                                    <select wire:model.live="{{ $filter['model'] }}">
                                        <option value=''>{{ $filter['placeholder'] }}</option>
                                        @foreach($filter['options'] as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                @elseif($filter['type'] === 'text')
                                    <input type="text" class="kt-input" placeholder="{{ $filter['placeholder'] }}" wire:model.live="{{ $filter['model'] }}" />
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- Custom Filters Slot --}}
                {{ $customFilters ?? '' }}

                {{-- Add Button --}}
                @if($showAddButton)
                    <button class="kt-btn kt-btn-outline flex items-center justify-center"
                            @if($addButtonModal) data-kt-modal-toggle="{{ $addButtonModal }}" @endif
                            @if($addButtonAction) wire:click="{{ $addButtonAction }}" @endif
                            title="{{ $addButtonText }}">
                        <i class="ki-filled ki-plus"></i>
                    </button>
                @endif
            </div>
        </div>

        {{-- Table Content --}}
        <div class="kt-card-table">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table kt-table-border table-fixed w-full">
                    <thead>
                        {{ $tableHeader }}
                    </thead>
                    <tbody>
                        {{ $tableBody }}
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer with Counter --}}
        @if($showCounter && $paginator)
            <div class="kt-card-footer flex-col justify-center gap-5 text-sm font-medium text-secondary-foreground md:flex-row md:justify-between">
                <div class="order-2 flex items-center gap-2 md:order-1">
                </div>
                <div class="order-1 flex items-center gap-4 md:order-2">
                    <span>
                        Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() ?? 0 }} {{ $counterText }}
                    </span>
                </div>
            </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if($paginator)
        <x-ktui-pagination :paginator="$paginator" />
    @endif
</div>