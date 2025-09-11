<div>
    @if($label)
        <label class="block text-sm font-medium mb-1">
            {{ $label }}
            @if($required)
                <span class="text-destructive">*</span>
            @endif
        </label>
    @endif

    <div class="flex gap-2 mb-2">
        <input
            type="text"
            class="kt-input flex-1"
            wire:model.live="currentInput"
            wire:keydown.enter.prevent="addItem"
            placeholder="{{ $placeholder }}"
        />
        <button
            type="button"
            class="kt-btn kt-btn-primary"
            wire:click="addItem"
            @disabled(empty(trim($currentInput)))
        >
            {{ $addButtonText }}
        </button>
    </div>

    @if(count($items) > 0)
        <div class="space-y-2">
            @foreach($items as $index => $item)
                <div class="flex items-center gap-2">
                    <input
                        type="text"
                        class="kt-input flex-1"
                        wire:model="items.{{ $index }}"
                        placeholder="Item {{ $index + 1 }}"
                    />
                    @if($reorderEnabled)
                        <button
                            type="button"
                            class="kt-btn kt-btn-outline"
                            wire:click="moveUp({{ $index }})"
                            title="Move up"
                            @disabled($index === 0)
                        >
                            <i class="ki-filled ki-arrow-up"></i>
                        </button>
                        <button
                            type="button"
                            class="kt-btn kt-btn-outline"
                            wire:click="moveDown({{ $index }})"
                            title="Move down"
                            @disabled($index === count($items) - 1)
                        >
                            <i class="ki-filled ki-arrow-down"></i>
                        </button>
                    @endif
                    <button
                        type="button"
                        class="kt-btn kt-btn-outline kt-btn-destructive text-destructive"
                        wire:click="removeItem({{ $index }})"
                        title="Remove item"
                    >
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
            @endforeach
        </div>
    @endif
</div>
