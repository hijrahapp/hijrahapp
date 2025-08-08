<div class="kt-modal hidden" data-kt-modal="true" id="module_add_modal" wire:ignore.self>
    <div class="kt-modal-content max-w-[800px] top-[5%]">
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">
                {{ $isEditMode ? 'Edit Module' : 'Add Module' }}
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

                <div>
                    <label class="block text-sm font-medium mb-1">Module Name <span class="text-destructive">*</span></label>
                    <input type="text" class="kt-input w-full" wire:model.defer="name" placeholder="Enter module name" />
                </div>
                @error('name')<span class="text-destructive text-xs">{{ $message }}</span>@enderror

                <div>
                    <label class="block text-sm font-medium mb-1">Description <span class="text-destructive">*</span></label>
                    <textarea class="kt-textarea w-full" rows="3" wire:model.defer="description" placeholder="Enter module description"></textarea>
                </div>
                @error('description')<span class="text-destructive text-xs">{{ $message }}</span>@enderror

                <div>
                    <label class="block text-sm font-medium mb-1">Definition <span class="text-destructive">*</span></label>
                    <textarea class="kt-textarea w-full" rows="3" wire:model.defer="definition" placeholder="Enter module definition"></textarea>
                </div>
                @error('definition')<span class="text-destructive text-xs">{{ $message }}</span>@enderror

                <div>
                    <label class="block text-sm font-medium mb-1">Objectives <span class="text-destructive">*</span></label>
                    <textarea class="kt-textarea w-full" rows="3" wire:model.defer="objectives" placeholder="Enter module objectives"></textarea>
                </div>
                @error('objectives')<span class="text-destructive text-xs">{{ $message }}</span>@enderror

                <!-- Tags -->
                <div>
                    <label class="block text-sm font-medium mb-1">Tags</label>
                    <div class="relative">
                        <div class="flex gap-2">
                            <input type="text" class="kt-input flex-1" wire:model.live="newTag" placeholder="Search or add tags" />
                            <button type="button" class="kt-btn kt-btn-outline" wire:click="addTag">
                                Add
                            </button>
                        </div>

                        @if($showTagSuggestions && count($tagSuggestions) > 0)
                            <div class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto">
                                @foreach($tagSuggestions as $suggestion)
                                    <button type="button" class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center justify-between" wire:click="selectTag({{ $suggestion['id'] }}, '{{ $suggestion['title'] }}')">
                                        <span>{{ $suggestion['title'] }}</span>
                                        <i class="ki-filled ki-plus text-xs"></i>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if(count($tags) > 0)
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach($tags as $tagId)
                                @php $tag = \App\Models\Tag::find($tagId); @endphp
                                @if($tag)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary border border-primary/20">
                                        {{ $tag->title }}
                                        <button type="button" wire:click="removeTag({{ $tagId }})" class="ml-1 hover:text-destructive">
                                            <i class="ki-filled ki-cross text-xs"></i>
                                        </button>
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
                @error('tags')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
            </div>
            <div class="kt-modal-footer flex gap-2 justify-end p-5">
                <button type="button" class="kt-btn kt-btn-outline" wire:click="closeModal">Cancel</button>
                <button type="submit" class="kt-btn kt-btn-primary">{{ $isEditMode ? 'Update Module' : 'Create Module' }}</button>
            </div>
        </form>
    </div>
</div>


