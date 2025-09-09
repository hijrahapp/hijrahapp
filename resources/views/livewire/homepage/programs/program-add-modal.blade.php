<div class="kt-modal hidden" data-kt-modal="true" id="program_add_modal" wire:ignore.self>
    <div class="kt-modal-content max-w-[800px] top-[10%] max-h-[96vh] overflow-y-auto">
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">
                {{ $isEditMode ? 'Edit Program' : 'Add Program' }}
            </span>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" wire:click="resetForm" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form wire:submit.prevent="saveProgram">
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
                        <input type="text" class="kt-input w-full" wire:model.defer="name" placeholder="Enter program name" />
                        @error('name')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    </div>

                    <div class="mt-4">
                        <livewire:shared.components.textarea :label="'Description'" wire:model.defer="description" :required="true" :placeholder="'Enter program description'" :rows="3" :maxlength="200" />
                        @error('description')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    </div>

                    <div class="mt-4">
                        <livewire:shared.components.textarea :label="'Definition'" wire:model.defer="definition" :required="true" :placeholder="'Enter program definition'" :rows="3" />
                        @error('definition')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium mb-1">Objectives</label>
                        <livewire:shared.components.rich-text :model="$objectives" wire:model="objectives" :placeholder="'Enter program objectives'" :editorId="'program_objectives_modal_editor'" :minHeight="'150px'" />
                        @error('objectives')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="kt-modal-footer flex gap-2 justify-end p-5">
                <button type="button" class="kt-btn kt-btn-outline" wire:click="resetForm" data-kt-modal-dismiss="true">Cancel</button>
                <button type="submit" class="kt-btn kt-btn-primary">{{ $isEditMode ? 'Update Program' : 'Create Program' }}</button>
            </div>
        </form>
    </div>
</div>
