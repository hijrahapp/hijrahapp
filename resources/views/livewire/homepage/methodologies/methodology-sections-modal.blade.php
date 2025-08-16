<div class="kt-modal hidden" data-kt-modal="true" id="methodology_sections_modal" wire:ignore.self>
    <div class="kt-modal-content max-w-[600px] top-[10%] max-h-[98vh] overflow-y-auto">
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">
                Manage Sections
            </span>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" wire:click="closeModal">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form wire:submit.prevent="save">
            <div class="kt-modal-body p-5 flex flex-col gap-6">
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

                <!-- Section 1 -->
                <div class="border-l-4 border-primary pl-4">
                    <h3 class="text-lg font-medium mb-4 text-primary">{{ $firstSectionName }}</h3>

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
                        <label class="block text-sm font-medium mb-1">Number of Pillars</label>
                        <input type="text" class="kt-input w-full" wire:model.defer="firstSectionNumberOfPillars" placeholder="Enter number of pillars" />
                        @error('firstSectionNumberOfPillars')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium mb-1">Pillars Definition</label>
                        <textarea class="kt-textarea w-full" rows="3" wire:model.defer="firstSectionPillarsDefinition" placeholder="Enter pillars definition"></textarea>
                        @error('firstSectionPillarsDefinition')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    </div>

                    <div class="mt-4">
                        <livewire:shared.components.image-picker
                            wire:model="firstSectionImgUrl"
                            :label="'Section 1 Image'"
                            :required="true"
                            key="{{ $methodologyId ? 'edit_section1' : 'new_section1' }}" />
                        @error('firstSectionImgUrl')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    </div>

                    <!-- General Questions Details for Section 1 -->
                    <div class="mt-6">
                        <h4 class="text-md font-medium mb-3">General Questions Details</h4>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Number of Questions</label>
                                <input type="text" class="kt-input w-full" wire:model.defer="firstSectionNumberOfQuestions" placeholder="Enter number of questions" />
                                @error('firstSectionNumberOfQuestions')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Minutes</label>
                                <input type="text" class="kt-input w-full" wire:model.defer="firstSectionMinutes" placeholder="Enter minutes" />
                                @error('firstSectionMinutes')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1">Brief</label>
                            <textarea class="kt-textarea w-full" rows="3" wire:model.defer="firstSectionBrief" placeholder="Enter brief description"></textarea>
                            @error('firstSectionBrief')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="border-l-4 border-t pt-6 border-primary pl-4">
                    <h3 class="text-lg font-medium mb-4 text-primary">{{ $secondSectionName }}</h3>

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
                        <label class="block text-sm font-medium mb-1">Number of Pillars</label>
                        <input type="text" class="kt-input w-full" wire:model.defer="secondSectionNumberOfPillars" placeholder="Enter number of pillars" />
                        @error('secondSectionNumberOfPillars')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium mb-1">Pillars Definition</label>
                        <textarea class="kt-textarea w-full" rows="3" wire:model.defer="secondSectionPillarsDefinition" placeholder="Enter pillars definition"></textarea>
                        @error('secondSectionPillarsDefinition')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    </div>

                    <div class="mt-4">
                        <livewire:shared.components.image-picker
                            wire:model="secondSectionImgUrl"
                            :label="'Section 2 Image'"
                            :required="true"
                            key="{{ $methodologyId ? 'edit_section2' : 'new_section2' }}" />
                        @error('secondSectionImgUrl')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    </div>

                    <!-- General Questions Details for Section 2 -->
                    <div class="mt-6">
                        <h4 class="text-md font-medium mb-3">General Questions Details</h4>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Number of Questions</label>
                                <input type="text" class="kt-input w-full" wire:model.defer="secondSectionNumberOfQuestions" placeholder="Enter number of questions" />
                                @error('secondSectionNumberOfQuestions')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Minutes</label>
                                <input type="text" class="kt-input w-full" wire:model.defer="secondSectionMinutes" placeholder="Enter minutes" />
                                @error('secondSectionMinutes')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1">Brief</label>
                            <textarea class="kt-textarea w-full" rows="3" wire:model.defer="secondSectionBrief" placeholder="Enter brief description"></textarea>
                            @error('secondSectionBrief')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="kt-modal-footer flex gap-2 justify-end p-5">
                <button type="button" class="kt-btn kt-btn-outline" wire:click="closeModal">Cancel</button>
                <button type="submit" class="kt-btn kt-btn-primary">Update Sections</button>
            </div>
        </form>
    </div>
</div>
