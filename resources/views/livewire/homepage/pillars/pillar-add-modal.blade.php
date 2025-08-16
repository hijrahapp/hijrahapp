<div class="kt-modal hidden" data-kt-modal="true" id="pillar_add_modal" wire:ignore.self>
    <div class="kt-modal-content max-w-[800px] top-[10%]">
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">
                {{ $isEditMode ? 'Edit Pillar' : 'Add Pillar' }}
            </span>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" wire:click="closeModal">
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

                <!-- Pillar Name -->
                <div>
                    <label class="block text-sm font-medium mb-1">Pillar Name <span class="text-destructive">*</span></label>
                    <input type="text" class="kt-input w-full" wire:model.defer="name" placeholder="Enter pillar name" />
                </div>
                @error('name')<span class="text-destructive text-xs">{{ $message }}</span>@enderror

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium mb-1">Description <span class="text-destructive">*</span></label>
                    <textarea class="kt-textarea w-full" rows="3" wire:model.defer="description" placeholder="Enter pillar description"></textarea>
                </div>
                @error('description')<span class="text-destructive text-xs">{{ $message }}</span>@enderror

                <!-- Definition -->
                <div>
                    <label class="block text-sm font-medium mb-1">Definition <span class="text-destructive">*</span></label>
                    <textarea class="kt-textarea w-full" rows="3" wire:model.defer="definition" placeholder="Enter pillar definition"></textarea>
                </div>
                @error('definition')<span class="text-destructive text-xs">{{ $message }}</span>@enderror

                <!-- Objectives -->
                <label class="block text-sm font-medium mb-1">Objectives <span class="text-destructive">*</span></label>
                <livewire:shared.components.rich-text :model="$objectives" wire:model="objectives" :placeholder="'Enter pillar objectives'" :editorId="'pillar_objectives_editor'" :minHeight="'150px'" />
                @error('objectives')<span class="text-destructive text-xs">{{ $message }}</span>@enderror

                <!-- Image -->
                <livewire:shared.components.image-picker
                    wire:model="imgUrl"
                    :label="'Pillar Image'"
                    :required="false"
                    key="{{ $pillarId ?: 'new' }}" />
                @error('imgUrl')<span class="text-destructive text-xs">{{ $message }}</span>@enderror

                <!-- Tags -->
                <livewire:shared.components.tag-picker wire:model="tags" :label="'Tags'" :placeholder="'Search or add tags'" :addButtonText="'Add'" />
                @error('tags')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
            </div>
            <div class="kt-modal-footer flex gap-2 justify-end p-5">
                <button type="button" class="kt-btn kt-btn-outline" wire:click="closeModal">
                    Cancel
                </button>
                <button type="submit" class="kt-btn kt-btn-primary">
                    {{ $isEditMode ? 'Update Pillar' : 'Create Pillar' }}
                </button>
            </div>
        </form>
    </div>
</div>
