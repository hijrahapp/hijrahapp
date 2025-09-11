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
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium">Todos</h3>
                        <button type="button" class="kt-btn kt-btn-outline kt-btn-sm" wire:click="addTodo">
                            <i class="ki-filled ki-plus"></i>
                            Add Todo
                        </button>
                    </div>

                    @if(count($todos) > 0)
                        <div class="space-y-3">
                            @foreach($todos as $index => $todo)
                                <div class="flex gap-2 items-start">
                                    <div class="flex-1">
                                        <input type="text" 
                                               class="kt-input w-full" 
                                               wire:model.defer="todos.{{ $index }}" 
                                               placeholder="Enter todo item" 
                                               maxlength="500" />
                                        @error("todos.{$index}")<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                    </div>
                                    <button type="button" 
                                            class="kt-btn kt-btn-outline kt-btn-sm text-destructive border-destructive hover:bg-destructive hover:text-white" 
                                            wire:click="removeTodo({{ $index }})">
                                        <i class="ki-filled ki-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-gray-500 text-center py-4">
                            No todos added yet. Click "Add Todo" to add items.
                        </div>
                    @endif
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