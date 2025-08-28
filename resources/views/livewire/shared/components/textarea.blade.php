<div class="w-full" wire:ignore>
    @if(!empty($label))
        <label class="block text-sm font-medium mb-1">{{ $label }} {!! !empty($required) ? '<span class="text-destructive">*</span>' : '' !!}</label>
    @endif

    <div class="relative" x-data="{ get val() { return $wire.get('model') }, set val(v) { $wire.set('model', v) }, max: {{ $maxlength ? (int) $maxlength : 'null' }} }">
        <textarea
            x-ref="ta"
            x-model="val"
            wire:model.defer="model"
            class="kt-textarea w-full"
            rows="{{ $rows ?? 3 }}"
            placeholder="{{ $placeholder ?? '' }}"
            @if($maxlength) maxlength="{{ $maxlength }}" @endif
        >{{ $model }}</textarea>

        <div x-show="max" class="pointer-events-none absolute right-2 bottom-2 text-xs text-gray-500" x-text="(val?.length || 0) + '/' + max"></div>
    </div>

    @isset($hint)
        <p class="text-xs text-secondary-foreground/70 mt-1">{{ $hint }}</p>
    @endisset
</div>


