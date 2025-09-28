<div>
    @if($label)
        <label class="block text-sm font-medium mb-1">{{ $label }} @if($required)<span class="text-destructive">*</span>@endif</label>
    @endif
    <div class="relative">
        <div class="flex gap-2">
            <input type="text" class="kt-input flex-1" wire:model.live="query" placeholder="{{ $placeholder }}" />
            <button type="button" class="kt-btn kt-btn-outline" wire:click="add">{{ $addButtonText }}</button>
        </div>

        @if($showSuggestions && count($suggestions) > 0)
            <div class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto">
                @foreach($suggestions as $s)
                    <button type="button" class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center justify-between" wire:click="select({{ $s['id'] }}, '{{ $s['name'] }}')">
                        <span>{{ $s['name'] }}</span>
                        <i class="ki-filled ki-plus text-xs"></i>
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    @if(count($this->selected) > 0)
        <div class="flex flex-wrap gap-2 mt-2">
            @foreach($this->selected as $id => $name)
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary border border-primary/20">
                    {{ $name }}
                    <button type="button" wire:click="remove({{ $id }})" class="ml-1 hover:text-destructive">
                        <i class="ki-filled ki-cross text-xs"></i>
                    </button>
                </span>
            @endforeach
        </div>
    @endif
</div>
