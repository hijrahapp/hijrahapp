{{-- Generic Form Field Component --}}
@props([
    'label',
    'name',
    'type' => 'text',
    'required' => false,
    'placeholder' => '',
    'model' => null,
    'options' => [],
    'rows' => 3,
    'maxlength' => null,
    'help' => null,
    'class' => '',
    'disabled' => false
])

<div class="mt-4">
    <label class="block text-sm font-medium mb-1">
        {{ $label }}
        @if($required)
            <span class="text-destructive">*</span>
        @endif
    </label>

    @if($help)
        <p class="text-sm text-gray-500 mb-2">{{ $help }}</p>
    @endif

    @switch($type)
        @case('textarea')
            <textarea
                class="kt-input w-full {{ $class }}"
                @if($model) wire:model.defer="{{ $model }}" @endif
                placeholder="{{ $placeholder }}"
                rows="{{ $rows }}"
                @if($maxlength) maxlength="{{ $maxlength }}" @endif
                @if($disabled) disabled @endif
            ></textarea>
            @break

        @case('select')
            <select
                class="kt-input w-full {{ $class }}"
                @if($model) wire:model.defer="{{ $model }}" @endif
                @if($disabled) disabled @endif
            >
                @if($placeholder)
                    <option value="">{{ $placeholder }}</option>
                @endif
                @foreach($options as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @break

        @case('file')
            <input
                type="file"
                class="kt-input w-full {{ $class }}"
                @if($model) wire:model="{{ $model }}" @endif
                @if($disabled) disabled @endif
            />
            @break

        @case('checkbox')
            <div class="flex items-center">
                <input
                    type="checkbox"
                    class="kt-checkbox {{ $class }}"
                    @if($model) wire:model.defer="{{ $model }}" @endif
                    @if($disabled) disabled @endif
                />
                <label class="ml-2 text-sm">{{ $placeholder }}</label>
            </div>
            @break

        @default
            <input
                type="{{ $type }}"
                class="kt-input w-full {{ $class }}"
                @if($model) wire:model.defer="{{ $model }}" @endif
                placeholder="{{ $placeholder }}"
                @if($maxlength) maxlength="{{ $maxlength }}" @endif
                @if($disabled) disabled @endif
            />
    @endswitch

    @if($name)
        @error($name)
            <span class="text-destructive text-xs">{{ $message }}</span>
        @enderror
    @endif
</div>