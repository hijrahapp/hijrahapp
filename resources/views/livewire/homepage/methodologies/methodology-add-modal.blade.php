<div class="kt-modal hidden" data-kt-modal="true" id="methodology_add_modal" wire:ignore.self>
    <div class="kt-modal-content max-w-[800px] top-[10%] max-h-[96vh] overflow-y-auto">
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">
                {{ $isEditMode ? 'Edit Methodology' : 'Add Methodology' }}
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

                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-medium mb-4">Basic Information</h3>

                    <div class="mt-4">
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
                            :required="true"
                            key="{{ $methodologyId ?: 'new' }}" />
                        @error('imgUrl')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium mb-1">Type <span class="text-destructive">*</span></label>
                        <select class="kt-select w-full" wire:model.live="type" @if($isEditMode) disabled @endif>
                            <option value="">Select Type</option>
                            <option value="simple">Simple</option>
                            <option value="complex">Complex</option>
                            <option value="twoSection">2-Section</option>
                        </select>
                        @error('type')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    </div>
                </div>

                @if($type)
                    <div class="kt-separator mt-4"></div>
                @endif

                <!-- Type-specific fields -->
                @if($type === 'simple')
                    <div>
                        <h3 class="text-lg font-medium mb-4">Simple Type Configuration</h3>
                        <div>
                            <label class="block text-sm font-medium mb-1">Modules Definition</label>
                            <textarea class="kt-textarea w-full" rows="3" wire:model.defer="modulesDefinition" placeholder="Enter modules definition (optional)"></textarea>
                            @error('modulesDefinition')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>
                    </div>
                @endif

                @if($type === 'complex')
                    <div>
                        <h3 class="text-lg font-medium mb-4">Complex Type Configuration</h3>
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
                @endif

                @if($type === 'twoSection')
                    <div>
                        <h3 class="text-lg font-medium mb-4">Two-Section Type Configuration</h3>

                        <!-- Section 1 -->
                        <div class="border-l-4 border-primary pl-4 mb-6">
                            <h4 class="text-md font-medium mb-3 text-primary">Section 1</h4>

                            <div class="mt-4">
                                    <label class="block text-sm font-medium mb-1">Name <span class="text-destructive">*</span></label>
                                    <input type="text" class="kt-input w-full" wire:model.defer="firstSectionName" placeholder="Enter section 1 name" />
                                    @error('firstSectionName')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium mb-1">Description <span class="text-destructive">*</span></label>
                                <textarea class="kt-textarea w-full" rows="2" wire:model.defer="firstSectionDescription" placeholder="Enter section 1 description"></textarea>
                                @error('firstSectionDescription')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium mb-1">Definition <span class="text-destructive">*</span></label>
                                <textarea class="kt-textarea w-full" rows="3" wire:model.defer="firstSectionDefinition" placeholder="Enter section 1 definition"></textarea>
                                @error('firstSectionDefinition')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium mb-1">Objectives</label>
                                <livewire:shared.components.rich-text :model="$firstSectionObjectives" wire:model="firstSectionObjectives" :placeholder="'Enter section 1 objectives'" :editorId="'section1_objectives_editor'" :minHeight="'120px'" />
                                @error('firstSectionObjectives')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>

                            <div class="mt-4">
                                <livewire:shared.components.image-picker
                                    wire:model="firstSectionImgUrl"
                                    :label="'Section 1 Image'"
                                    :required="true"
                                    key="{{ $methodologyId ? 'edit_section1' : 'new_section1' }}" />
                                @error('firstSectionImgUrl')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <!-- Section 2 -->
                        <div class="border-l-4 border-secondary pl-4">
                            <h4 class="text-md font-medium mb-3 text-secondary">Section 2</h4>

                            <div class="mt-4">
                                <label class="block text-sm font-medium mb-1">Name <span class="text-destructive">*</span></label>
                                <input type="text" class="kt-input w-full" wire:model.defer="secondSectionName" placeholder="Enter section 2 name" />
                                @error('secondSectionName')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium mb-1">Description <span class="text-destructive">*</span></label>
                                <textarea class="kt-textarea w-full" rows="2" wire:model.defer="secondSectionDescription" placeholder="Enter section 2 description"></textarea>
                                @error('secondSectionDescription')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium mb-1">Definition <span class="text-destructive">*</span></label>
                                <textarea class="kt-textarea w-full" rows="3" wire:model.defer="secondSectionDefinition" placeholder="Enter section 2 definition"></textarea>
                                @error('secondSectionDefinition')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium mb-1">Objectives</label>
                                <livewire:shared.components.rich-text :model="$secondSectionObjectives" wire:model="secondSectionObjectives" :placeholder="'Enter section 2 objectives'" :editorId="'section2_objectives_editor'" :minHeight="'120px'" />
                                @error('secondSectionObjectives')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>

                            <div class="mt-4">
                                <livewire:shared.components.image-picker
                                    wire:model="secondSectionImgUrl"
                                    :label="'Section 2 Image'"
                                    :required="true"
                                    key="{{ $methodologyId ? 'edit_section2' : 'new_section2' }}" />
                                @error('secondSectionImgUrl')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="kt-modal-footer flex gap-2 justify-end p-5">
                <button type="button" class="kt-btn kt-btn-outline" wire:click="closeModal">Cancel</button>
                <button type="submit" class="kt-btn kt-btn-primary">{{ $isEditMode ? 'Update Methodology' : 'Create Methodology' }}</button>
            </div>
        </form>
    </div>
</div>
