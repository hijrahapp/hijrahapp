<div class="kt-modal hidden" data-kt-modal="true" id="methodology_module_add_modal" wire:ignore.self>
    <div class="kt-modal-content max-w-[600px] top-[15%] max-h-[96vh] overflow-y-auto">
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">{{ $isEditMode ? 'Edit Module' : 'Add Module' }}</span>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" wire:click="closeModal">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form wire:submit.prevent="save">
            <div class="kt-modal-body p-5 flex flex-col gap-4">
                <div class="{{ $isEditMode ? 'opacity-60 pointer-events-none' : '' }}">
                    <label class="block text-sm font-medium mb-1">Module <span class="text-destructive">*</span></label>
                    @if($isEditMode)
                        <input type="text" class="kt-input w-full" value="{{ $moduleName }}" disabled />
                    @else
                        <div class="relative">
                            <div class="kt-input">
                                <i class="ki-filled ki-element-7"></i>
                                <input type="text" class="kt-input" placeholder="Search active modules" wire:model.live="moduleSearch" />
                            </div>
                            @if($showModuleSuggestions && count($moduleSuggestions) > 0)
                                <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                                    @foreach($moduleSuggestions as $suggestion)
                                        <button type="button" class="w-full text-left px-4 py-2 hover:bg-gray-100" wire:click="selectModule({{ $suggestion['id'] }}, '{{ $suggestion['name'] }}')">
                                            {{ $suggestion['name'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @error('selectedModuleId')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    @endif
                </div>

                @if($enablePillarSelection)
                <div>
                    <label class="block text-sm font-medium mb-1">Pillar <span class="text-destructive">*</span></label>
                    <div class="relative">
                        <div class="kt-input">
                            <i class="ki-filled ki-filter"></i>
                            <input type="text" class="kt-input" placeholder="Search methodology pillars" wire:model.live="pillarSearch" />
                        </div>
                        @if($showPillarSuggestions && count($pillarSuggestions) > 0)
                            <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                                @foreach($pillarSuggestions as $suggestion)
                                    <button type="button" class="w-full text-left px-4 py-2 hover:bg-gray-100" wire:click="selectPillar({{ $suggestion['id'] }}, '{{ $suggestion['name'] }}')">
                                        {{ $suggestion['name'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @error('selectedPillarId')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium mb-1">Weight <span class="text-destructive">*</span></label>
                    <input type="number" class="kt-input w-full" wire:model.defer="weight" placeholder="Enter weight" />
                    @error('weight')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Estimated Questions Time (minutes) <span class="text-destructive">*</span></label>
                    <input type="number" min="0" class="kt-input w-full" wire:model.defer="questionsEstimatedTime" placeholder="Enter estimated time in minutes" />
                    @error('questionsEstimatedTime')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Questions Description (optional)</label>
                    <textarea class="kt-textarea w-full" rows="3" wire:model.defer="questionsDescription" placeholder="Enter a general description of this module's questions"></textarea>
                    @error('questionsDescription')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Report (optional)</label>
                    <textarea class="kt-textarea w-full" rows="3" wire:model.defer="report" placeholder="Enter report"></textarea>
                    @error('report')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                </div>

                <div class="{{ $isEditMode ? 'opacity-60 pointer-events-none' : '' }}">
                    <label class="block text-sm font-medium mb-1">Dependency (optional)</label>
                    <p class="text-xs text-secondary-foreground/70 mb-2">Select one module that must be completed before users can access this module.</p>
                    <div class="relative">
                        <div class="kt-input">
                            <i class="ki-filled ki-filter"></i>
                            <input type="text" class="kt-input pr-10" placeholder="Search linked modules" wire:model.live="dependencySearch" @if($isEditMode) disabled @endif />
                            @if(count($dependencyIds) === 1 && !$isEditMode)
                                <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-secondary-foreground/60 hover:text-secondary-foreground" wire:click="clearDependency">
                                    <i class="ki-filled ki-cross"></i>
                                </button>
                            @endif
                        </div>
                        @if($showDependencySuggestions && count($dependencySuggestions) > 0)
                            <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                                @foreach($dependencySuggestions as $suggestion)
                                    <button type="button" class="w-full flex items-center justify-between px-4 py-2 hover:bg-gray-100" wire:click="toggleDependency({{ $suggestion['id'] }}, '{{ $suggestion['name'] }}')" @if($isEditMode) disabled @endif>
                                        <span>{{ $suggestion['name'] }}</span>
                                        @if(in_array($suggestion['id'], $dependencyIds, true))
                                            <i class="ki-filled ki-check text-primary"></i>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="kt-modal-footer flex gap-2 justify-end p-5">
                <button type="button" class="kt-btn kt-btn-outline" wire:click="closeModal">Cancel</button>
                <button type="submit" class="kt-btn kt-btn-primary">{{ $isEditMode ? 'Update' : 'Add' }}</button>
            </div>
        </form>
    </div>
 </div>


