<div class="kt-container-fixed kt-main-container" id="methodologyManageContainer">
    <div class="flex flex-wrap items-center gap-2 pb-4">
        <button wire:click="backToList" class="kt-btn kt-btn-outline flex items-center justify-center">
            <i class="ki-filled ki-arrow-left"></i>
        </button>
        <h1 class="text-xl font-medium leading-none text-mono">
            Manage Methodology
        </h1>
    </div>

    {{-- Validation errors are shown via toasts; no inline error blocks --}}

    <div class="grid grid-cols-1 gap-6 pb-4">
        <!-- Part 1: Basic Information (Collapsible) -->
        <div data-kt-accordion="true" class="kt-accordion" id="methodology_manage_accordion">
            <div class="kt-card kt-accordion-item active" data-kt-accordion-item="true">
                <button
                    id="basic_info_toggle"
                    data-kt-accordion-toggle="true"
                    aria-controls="basic_info_content"
                    class="kt-accordion-toggle kt-card-header"
                >
                    <h3 class="kt-card-title">Basic Information</h3>
                    <span aria-hidden="true" class="kt-accordion-indicator">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus kt-accordion-indicator-on" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus kt-accordion-indicator-off" aria-hidden="true"><path d="M5 12h14"></path></svg>
                    </span>
                </button>
                <div class="kt-accordion-content" aria-labelledby="basic_info_toggle" id="basic_info_content">
                    <div class="kt-card-body p-5">
                        <div>
                            <label class="block text-sm font-medium mb-1">Name <span class="text-destructive">*</span></label>
                            <input type="text" class="kt-input w-full" wire:model.defer="name" placeholder="Enter methodology name" />
                            @error('name')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1">Description <span class="text-destructive">*</span></label>
                            <textarea class="kt-textarea w-full" rows="3" wire:model.defer="description" placeholder="Enter methodology description"></textarea>
                            @error('description')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1">Definition <span class="text-destructive">*</span></label>
                            <textarea class="kt-textarea w-full" rows="3" wire:model.defer="definition" placeholder="Enter methodology definition"></textarea>
                            @error('definition')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1">Objectives</label>
                            <livewire:shared.components.rich-text :model="$objectives" wire:model="objectives" :placeholder="'Enter methodology objectives'" :editorId="'methodology_objectives_editor'" :minHeight="'150px'" />
                            @error('objectives')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>

                        <div class="mt-4">
                            <livewire:shared.components.tag-picker wire:model="tags" :label="'Tags'" :placeholder="'Search or add tags'" :addButtonText="'Add'" />
                            @error('tags')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>

                        <div class="mt-4">
                            <livewire:shared.components.image-picker
                                wire:model="imgUrl"
                                :label="'Methodology Image'"
                                :required="false"
                                key="{{ $methodologyId ?: 'edit' }}" />
                            @error('imgUrl')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1">Type <span class="text-destructive">*</span></label>
                            <select class="kt-select w-full" wire:model="type" disabled>
                                <option value="">Select Type</option>
                                <option value="simple">Simple</option>
                                <option value="complex">Complex</option>
                                <option value="twoSection">2-Section</option>
                            </select>
                            @error('type')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="kt-card-footer flex items-center justify-end gap-2">
                        <button class="kt-btn kt-btn-primary" wire:click="saveBasicDetails">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Part 2 & 3: Placeholders based on type -->
        @if($type === 'simple')

            <div data-kt-accordion="true" class="kt-accordion">
                <div class="kt-card kt-accordion-item active" data-kt-accordion-item="true">
                    <button
                        id="extra_details_toggle"
                        data-kt-accordion-toggle="true"
                        aria-controls="extra_details_content"
                        class="kt-accordion-toggle kt-card-header"
                    >
                        <h3 class="kt-card-title">Extra Details</h3>
                        <span aria-hidden="true" class="kt-accordion-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus kt-accordion-indicator-on" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus kt-accordion-indicator-off" aria-hidden="true"><path d="M5 12h14"></path></svg>
                        </span>
                    </button>
                    <div class="kt-accordion-content" aria-labelledby="extra_details_toggle" id="extra_details_content">
                        <div class="kt-card-body p-5">
                            <div>
                                <label class="block text-sm font-medium mb-1">Modules Definition</label>
                                <textarea class="kt-textarea w-full" rows="3" wire:model.defer="modulesDefinition" placeholder="Enter modules definition (optional)"></textarea>
                                @error('modulesDefinition')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="kt-card-footer flex items-center justify-end gap-2">
                            <button class="kt-btn kt-btn-primary" wire:click="saveExtraDetails">Save</button>
                        </div>
                    </div>
                </div>
            </div>

            <div data-kt-accordion="true" class="kt-accordion">
                <div class="kt-card kt-accordion-item active" data-kt-accordion-item="true">
                    <button
                        id="modules_toggle"
                        data-kt-accordion-toggle="true"
                        aria-controls="modules_content"
                        class="kt-accordion-toggle kt-card-header"
                    >
                        <h3 class="kt-card-title">Modules</h3>
                        <span aria-hidden="true" class="kt-accordion-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus kt-accordion-indicator-on" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus kt-accordion-indicator-off" aria-hidden="true"><path d="M5 12h14"></path></svg>
                        </span>
                    </button>
                    <div class="kt-accordion-content" aria-labelledby="modules_toggle" id="modules_content">
                        <div class="kt-card-body p-5">
                            <livewire:homepage.methodologies.methodology-modules.methodology-modules-table :methodologyId="$methodologyId" />
                            <livewire:homepage.methodologies.methodology-modules.methodology-module-questions-modal />
                            <livewire:homepage.methodologies.methodology-modules.methodology-module-add-modal />
                        </div>
                    </div>
                </div>
            </div>

        @elseif($type === 'complex')

            <div data-kt-accordion="true" class="kt-accordion">
                <div class="kt-card kt-accordion-item active" data-kt-accordion-item="true">
                    <button
                        id="extra_details_toggle"
                        data-kt-accordion-toggle="true"
                        aria-controls="extra_details_content"
                        class="kt-accordion-toggle kt-card-header"
                    >
                        <h3 class="kt-card-title">Extra Details</h3>
                        <span aria-hidden="true" class="kt-accordion-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus kt-accordion-indicator-on" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus kt-accordion-indicator-off" aria-hidden="true"><path d="M5 12h14"></path></svg>
                        </span>
                    </button>
                    <div class="kt-accordion-content" aria-labelledby="extra_details_toggle" id="extra_details_content">
                        <div class="kt-card-body p-5">
                            <div class="mt-4">
                                <label class="block text-sm font-medium mb-1">Pillars Definition</label>
                                <textarea class="kt-textarea w-full" rows="3" wire:model.defer="pillarsDefinition" placeholder="Enter pillars definition (optional)"></textarea>
                                @error('pillarsDefinition')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>
                            <div class="mt-4">
                                <label class="block text-sm font-medium mb-1">Number of Pillars</label>
                                <input type="text" class="kt-input w-full" wire:model.defer="numberOfPillars" placeholder="Enter number of pillars" />
                                @error('numberOfPillars')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="kt-card-footer flex items-center justify-end gap-2">
                            <button class="kt-btn kt-btn-primary" wire:click="saveExtraDetails">Save</button>
                        </div>
                    </div>
                </div>
            </div>

            <div data-kt-accordion="true" class="kt-accordion">
                <div class="kt-card kt-accordion-item active" data-kt-accordion-item="true">
                    <button
                        id="pillars_toggle"
                        data-kt-accordion-toggle="true"
                        aria-controls="pillars_content"
                        class="kt-accordion-toggle kt-card-header"
                    >
                        <h3 class="kt-card-title">Pillars</h3>
                        <span aria-hidden="true" class="kt-accordion-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus kt-accordion-indicator-on" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus kt-accordion-indicator-off" aria-hidden="true"><path d="M5 12h14"></path></svg>
                        </span>
                    </button>
                    <div class="kt-accordion-content" aria-labelledby="pillars_toggle" id="pillars_content">
                        <div class="kt-card-body p-5">
                            <livewire:homepage.methodologies.methodology-pillars.methodology-pillars-table :methodologyId="$methodologyId" />
                            <livewire:homepage.methodologies.methodology-pillars.methodology-pillar-add-modal />
                        </div>
                    </div>
                </div>
            </div>

        @elseif($type === 'twoSection')

            <div data-kt-accordion="true" class="kt-accordion">
                <div class="kt-card kt-accordion-item active" data-kt-accordion-item="true">
                    <button
                        id="section1_toggle"
                        data-kt-accordion-toggle="true"
                        aria-controls="section1_content"
                        class="kt-accordion-toggle kt-card-header"
                    >
                        <h3 class="kt-card-title">Section 1 Details</h3>
                        <span aria-hidden="true" class="kt-accordion-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus kt-accordion-indicator-on" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus kt-accordion-indicator-off" aria-hidden="true"><path d="M5 12h14"></path></svg>
                        </span>
                    </button>
                    <div class="kt-accordion-content" aria-labelledby="section1_toggle" id="section1_content">
                        <div class="kt-card-body p-5">
                            <div>
                                <label class="block text-sm font-medium mb-1">Name <span class="text-destructive">*</span></label>
                                <input type="text" class="kt-input w-full" wire:model.defer="firstSectionName" placeholder="Enter section 1 name" />
                                @error('firstSectionName')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="kt-card-footer flex items-center justify-end gap-2">
                            <button class="kt-btn kt-btn-primary" wire:click="saveSection1Details">Save</button>
                        </div>
                    </div>
                </div>
            </div>

            <div data-kt-accordion="true" class="kt-accordion">
                <div class="kt-card kt-accordion-item active" data-kt-accordion-item="true">
                    <button
                        id="section2_toggle"
                        data-kt-accordion-toggle="true"
                        aria-controls="section2_content"
                        class="kt-accordion-toggle kt-card-header"
                    >
                        <h3 class="kt-card-title">Section 2 Details</h3>
                        <span aria-hidden="true" class="kt-accordion-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus kt-accordion-indicator-on" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus kt-accordion-indicator-off" aria-hidden="true"><path d="M5 12h14"></path></svg>
                        </span>
                    </button>
                    <div class="kt-accordion-content" aria-labelledby="section2_toggle" id="section2_content">
                        <div class="kt-card-body p-5">
                            <div>
                                <label class="block text-sm font-medium mb-1">Name <span class="text-destructive">*</span></label>
                                <input type="text" class="kt-input w-full" wire:model.defer="secondSectionName" placeholder="Enter section 2 name" />
                                @error('secondSectionName')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="kt-card-footer flex items-center justify-end gap-2">
                            <button class="kt-btn kt-btn-primary" wire:click="saveSection2Details">Save</button>
                        </div>
                    </div>
                </div>
            </div>

        @endif

        <!-- General Questions (always visible) -->
        <div data-kt-accordion="true" class="kt-accordion">
            <div class="kt-card kt-accordion-item active" data-kt-accordion-item="true">
                <button
                    id="general_questions_toggle"
                    data-kt-accordion-toggle="true"
                    aria-controls="general_questions_content"
                    class="kt-accordion-toggle kt-card-header"
                >
                    <h3 class="kt-card-title">General Questions</h3>
                    <span aria-hidden="true" class="kt-accordion-indicator">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus kt-accordion-indicator-on" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus kt-accordion-indicator-off" aria-hidden="true"><path d="M5 12h14"></path></svg>
                    </span>
                </button>
                <div class="kt-accordion-content" aria-labelledby="general_questions_toggle" id="general_questions_content">
                    <div class="kt-card-body p-5">
                        <livewire:homepage.methodologies.general-questions-table :methodologyId="$methodologyId" />
                        <livewire:homepage.methodologies.methodology-modules.methodology-module-questions-modal />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


