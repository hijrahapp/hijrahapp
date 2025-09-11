<div class="kt-modal hidden" data-kt-modal="true" id="liability_add_modal" wire:ignore.self>
    <div class="kt-modal-content max-w-[800px] top-[5%] max-h-[96vh] overflow-y-auto">
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">
                {{ $isEditMode ? 'Edit Liability' : 'Add Liability' }}
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
                        <input type="text" class="kt-input w-full" wire:model.defer="name" placeholder="Enter liability name" />
                        @error('name')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium mb-1">Description <span class="text-destructive">*</span></label>
                        <textarea class="kt-textarea w-full" rows="3" wire:model.defer="description" placeholder="Enter liability description" maxlength="200"></textarea>
                        @error('description')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium mb-1">Title <span class="text-destructive">*</span></label>
                        <input type="text" class="kt-input w-full" wire:model.defer="title" placeholder="Enter liability title" />
                        @error('title')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium mb-1">Header</label>
                        <textarea class="kt-textarea w-full" rows="2" wire:model.defer="header" placeholder="Enter liability header"></textarea>
                        @error('header')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    </div>
                </div>

                <!-- Todos Section -->
                <div>
                    <h3 class="text-lg font-medium mb-4">Todos</h3>
                    <livewire:shared.components.list-input 
                        :items="$todos" 
                        label="Todo Items" 
                        placeholder="Enter todo item" 
                        addButtonText="Add Todo"
                        identifier="todos"
                        :reorderEnabled="true" />
                </div>
            </div>
            <div class="kt-modal-footer flex justify-end gap-2 py-4 px-5">
                <button type="button" class="kt-btn kt-btn-dim" wire:click="resetForm" data-kt-modal-dismiss="true">Cancel</button>
                <button type="submit" class="kt-btn kt-btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ $isEditMode ? 'Update' : 'Save' }}</span>
                    <span wire:loading>Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>