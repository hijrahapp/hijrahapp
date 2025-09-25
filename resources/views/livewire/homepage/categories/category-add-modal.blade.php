<div class="kt-modal hidden" data-kt-modal="true" id="category_add_modal" wire:ignore.self>
    <div class="kt-modal-content max-w-[600px] top-[5%] max-h-[96vh] overflow-y-auto">
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">
                {{ $isEditMode ? 'Edit Category' : 'Add Category' }}
            </span>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" wire:click="resetForm" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form wire:submit.prevent="save">
            <div class="kt-modal-body p-5 flex flex-col gap-4">
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

                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-medium mb-4">Basic Information</h3>

                    <div class="mt-4">
                        <label class="block text-sm font-medium mb-1">Name <span class="text-destructive">*</span></label>
                        <input type="text" class="kt-input w-full" wire:model.defer="name" placeholder="Enter category name" />
                        @error('name')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    </div>

                    <div class="mt-4">
                        <livewire:shared.components.image-picker 
                            wire:model="icon" 
                            :placeholder="'/assets/media/avatars/blank.png'" 
                            label="Category Icon" 
                            :required="true" />
                        @error('icon')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
            <div class="kt-modal-footer flex justify-end gap-2 py-4 px-5">
                <button type="button" class="kt-btn kt-btn-outline" wire:click="resetForm" data-kt-modal-dismiss="true">Cancel</button>
                @if(!$isEditMode)
                    <button type="button" class="kt-btn kt-btn-outline" wire:click="saveAndAddNew" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveAndAddNew">Save and Add New</span>
                        <span wire:loading wire:target="saveAndAddNew">Saving...</span>
                    </button>
                @endif
                <button type="submit" class="kt-btn kt-btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ $isEditMode ? 'Update' : 'Save' }}</span>
                    <span wire:loading>Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>
