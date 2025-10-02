<div class="kt-container-fixed kt-main-container" id="enrichmentManageContainer">
    <div class="flex flex-wrap items-center gap-2 pb-4">
        <button wire:click="backToList" class="kt-btn kt-btn-outline flex items-center justify-center">
            <i class="ki-filled ki-arrow-left"></i>
        </button>
        <h1 class="text-xl font-medium leading-none text-mono">
            Manage Enrichment
        </h1>
    </div>

    <div class="grid grid-cols-1 gap-6 pb-4">
        <!-- Enrichment Details Section -->
        <div data-kt-accordion="true" class="kt-accordion" id="enrichment_manage_accordion">
            <div class="kt-card kt-accordion-item active" data-kt-accordion-item="true">
                <button
                    id="basic_info_toggle"
                    data-kt-accordion-toggle="true"
                    aria-controls="basic_info_content"
                    class="kt-accordion-toggle kt-card-header"
                >
                    <h3 class="kt-card-title">Enrichment Details</h3>
                    <span aria-hidden="true" class="kt-accordion-indicator">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus kt-accordion-indicator-on" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus kt-accordion-indicator-off" aria-hidden="true"><path d="M5 12h14"></path></svg>
                    </span>
                </button>
                <div class="kt-accordion-content" aria-labelledby="basic_info_toggle" id="basic_info_content" x-data="{ dirty: $wire.entangle('isBasicDirty') }" x-on:input="dirty = true" x-on:change="dirty = true" x-on:section-saved.window="if ($event.detail.section === 'basic') dirty = false">
                    <div class="kt-card-body p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Title -->
                            <div class="col-span-2">
                                <livewire:shared.components.textarea 
                                    :label="'Title'" 
                                    wire:model.defer="title" 
                                    :required="true" 
                                    :placeholder="'Enter enrichment title'" 
                                    :rows="2" 
                                    :maxlength="100" />
                                @error('title')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>

                            <!-- Description -->
                            <div class="col-span-2">
                                <livewire:shared.components.textarea 
                                    :label="'Description'" 
                                    wire:model.defer="description" 
                                    :required="true" 
                                    :placeholder="'Enter enrichment description'" 
                                    :rows="3" 
                                    :maxlength="200" />
                                @error('description')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>

                            <!-- Type (Disabled) -->
                            <div >
                                <label class="block text-sm font-medium mb-1">Type <span class="text-destructive">*</span></label>
                                <select class="kt-input w-full" disabled>
                                    <option value="{{ $type }}">{{ $this->enrichmentTypes[$type] ?? $type }}</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Type cannot be changed after creation</p>
                            </div>
                            
                            <!-- Image -->
                            <div class="col-span-2">
                                <livewire:shared.components.image-picker
                                    wire:model="imgUrl"
                                    :label="'Enrichment Image'"
                                    :required="true"
                                    key="{{ $enrichmentId }}-img" />
                                @error('imgUrl')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>


                            <!-- Categories -->
                            <div>
                                <livewire:shared.components.category-picker 
                                    wire:model="categories" 
                                    :label="'Categories'" 
                                    :placeholder="'Search or add categories'" 
                                    :addButtonText="'Add'" 
                                    :required="true"
                                    key="{{ $enrichmentId }}-categories" />
                                @error('categories')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>

                            <!-- Interests -->
                            <div>
                                <livewire:shared.components.interest-picker 
                                    wire:model="interests" 
                                    :label="'Interests'" 
                                    :placeholder="'Search or add interests'" 
                                    :addButtonText="'Add'" 
                                    :required="true"
                                    key="{{ $enrichmentId }}-interests" />
                                @error('interests')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>

                            <!-- Tags -->
                            <div class="col-span-2">
                                <livewire:shared.components.tag-picker 
                                    wire:model="tags" 
                                    :label="'Tags'" 
                                    :placeholder="'Search or add tags'" 
                                    :addButtonText="'Add'"
                                    key="{{ $enrichmentId }}-tags" />
                                @error('tags')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>

                        </div>
                    </div>
                    <div class="kt-card-footer flex items-center justify-end gap-2">
                        <button class="kt-btn kt-btn-primary disabled:opacity-50 disabled:cursor-not-allowed" wire:click="saveBasicDetails" :disabled="!dirty" wire:loading.attr="disabled" wire:target="saveBasicDetails">Save Details</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Details Section -->
        <div data-kt-accordion="true" class="kt-accordion">
            <div class="kt-card kt-accordion-item active" data-kt-accordion-item="true">
                <button
                    id="content_details_toggle"
                    data-kt-accordion-toggle="true"
                    aria-controls="content_details_content"
                    class="kt-accordion-toggle kt-card-header"
                >
                    <h3 class="kt-card-title">Content Details</h3>
                    <span aria-hidden="true" class="kt-accordion-indicator">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus kt-accordion-indicator-on" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus kt-accordion-indicator-off" aria-hidden="true"><path d="M5 12h14"></path></svg>
                    </span>
                </button>
                <div class="kt-accordion-content" aria-labelledby="content_details_toggle" id="content_details_content" x-data="{ dirty: $wire.entangle('isContentDirty') }" x-on:input="dirty = true" x-on:change="dirty = true" x-on:section-saved.window="if ($event.detail.section === 'content') dirty = false">
                    <div class="kt-card-body p-5">
                        <div class="grid grid-cols-1 gap-4">

                            <!-- Content (for article type) -->
                            @if($type === 'article')
                                <div>
                                    <label class="block text-sm font-medium mb-1">Content <span class="text-destructive">*</span></label>
                                    <livewire:shared.components.rich-text 
                                        wire:model="content" 
                                        :placeholder="'Enter article content...'" 
                                        :editorId="'enrichment_content_editor_' . $enrichmentId" 
                                        :minHeight="'200px'"
                                        wire:key="{{ $enrichmentId }}-content-{{ $type }}" />
                                    @error('content')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                </div>
                            @endif

                            <!-- Content URL and Cover Image (for video, audio, book, short-video) -->
                            @if(in_array($type, ['video', 'audio', 'book', 'short-video']))
                                <div>
                                    <livewire:shared.components.file-picker 
                                        :label="'Content File'" 
                                        :required="true"
                                        :placeholder="'Enter ' . $type . ' URL or upload a file'"
                                        wire:model="contentUrl" 
                                        :fileType="$type"
                                        :maxSize="50"
                                        :helpText="'Upload a ' . $type . ' file or provide a URL'"
                                        :enableValidation="true"
                                        :validateOnUpdate="false"
                                        wire:key="{{ $enrichmentId }}-content-{{ $type }}" />
                                    @error('contentUrl')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                </div>

                                <!-- Content Cover Image -->
                                <div>
                                    <livewire:shared.components.image-picker
                                        wire:model="contentImage"
                                        :label="'Content Cover Image'"
                                        :required="true"
                                        key="{{ $enrichmentId }}-cover-{{ $type }}" />
                                    @error('contentImage')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="kt-card-footer flex items-center justify-end gap-2">
                        <button class="kt-btn kt-btn-primary disabled:opacity-50 disabled:cursor-not-allowed" wire:click="saveContentDetails" :disabled="!dirty" wire:loading.attr="disabled" wire:target="saveContentDetails">Save Content</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
