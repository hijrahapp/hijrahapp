{{-- Generic Modal Form Component --}}
@props([
    'modalId',
    'title',
    'isEditMode' => false,
    'addTitle' => 'Add',
    'editTitle' => 'Edit',
    'maxWidth' => '800px',
    'topPosition' => '10%',
    'submitAction' => 'save',
    'submitText' => null,
    'cancelText' => 'Cancel',
    'showCancel' => true,
    'error' => null,
    'footerSlot' => null
])

<div class="kt-modal hidden" data-kt-modal="true" id="{{ $modalId }}" wire:ignore.self>
    <div class="kt-modal-content max-w-[{{ $maxWidth }}] top-[{{ $topPosition }}] max-h-[96vh] overflow-y-auto">
        {{-- Header --}}
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">
                @if($title)
                    {{ $title }}
                @else
                    {{ $isEditMode ? $editTitle : $addTitle }}
                @endif
            </span>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" wire:click="closeModal" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>

        {{-- Form --}}
        <form wire:submit.prevent="{{ $submitAction }}">
            <div class="kt-modal-body p-5 flex flex-col gap-4">
                {{-- Error Handling --}}
                @if($error)
                    <div class="bg-destructive/10 border border-destructive/20 text-destructive px-4 py-3 rounded-lg">
                        {{ $error }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-destructive/10 border border-destructive/20 text-destructive px-4 py-3 rounded-lg">
                        <div class="font-medium">Please correct the following errors:</div>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form Content --}}
                {{ $slot }}
            </div>

            {{-- Footer --}}
            <div class="kt-modal-footer flex gap-2 justify-end p-5">
                @if($footerSlot)
                    {{ $footerSlot }}
                @else
                    @if($showCancel)
                        <button type="button" class="kt-btn kt-btn-outline" wire:click="closeModal" data-kt-modal-dismiss="true">
                            {{ $cancelText }}
                        </button>
                    @endif
                    <button type="submit" class="kt-btn kt-btn-primary">
                        @if($submitText)
                            {{ $submitText }}
                        @else
                            {{ $isEditMode ? 'Update' : 'Create' }}
                        @endif
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>