<div class="kt-modal hidden" data-kt-modal="true" id="tag_add_modal" wire:ignore.self>
    <div class="kt-modal-content max-w-[600px] top-[15%]">
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">Add Tags</span>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" wire:click="closeModal">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form wire:submit.prevent="saveTag">
            <div class="kt-modal-body p-5 flex flex-col gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Add Tags <span class="text-destructive">*</span></label>
                    <div class="flex gap-2">
                        <input 
                            type="text" 
                            class="kt-input flex-1" 
                            wire:model.defer="newTitle" 
                            wire:keydown.enter.prevent="addTitle"
                            placeholder="Type tag name and press Enter"
                        />
                        <button 
                            type="button" 
                            class="kt-btn kt-btn-primary" 
                            wire:click="addTitle"
                        >
                            Add
                        </button>
                    </div>
                </div>

                @if(count($titles) > 0)
                    <div>
                        <label class="block text-sm font-medium mb-2">Tags to be added:</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($titles as $index => $title)
                                <div class="flex items-center gap-1 bg-primary/10 text-primary px-3 py-1 rounded-full">
                                    <span class="text-sm">{{ $title }}</span>
                                    <button 
                                        type="button" 
                                        class="text-primary hover:text-destructive ml-1" 
                                        wire:click="removeTitle({{ $index }})"
                                    >
                                        <i class="ki-filled ki-cross text-xs"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @error('titles')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                @error('titles.*')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                
                @if($error)
                    <div class="kt-error-message text-destructive bg-destructive/10 p-3 rounded">
                        {{ $error }}
                    </div>
                @endif

                <div class="flex justify-end mt-4">
                    <button type="button" class="kt-btn kt-btn-outline me-2" wire:click="closeModal">Cancel</button>
                    <button type="submit" class="kt-btn kt-btn-primary" @if(count($titles) == 0) disabled @endif>Save Tags</button>
                </div>
            </div>
        </form>
    </div>
</div>
