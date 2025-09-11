<div class="kt-modal hidden" data-kt-modal="true" id="liability_module_add_modal" wire:ignore.self>
    <div class="kt-modal-content max-w-[800px] top-[15%] max-h-[96vh] overflow-y-auto">
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">
                Add Module to Liability
            </span>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" wire:click="closeModal" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form wire:submit.prevent="addModule">
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

                <!-- Filter Section -->
                <div>
                    <h3 class="text-lg font-medium mb-4">Select Methodology & Pillar</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Methodology Selection -->
                        <div>
                            <label class="block text-sm font-medium mb-1">Methodology <span class="text-destructive">*</span></label>
                            @if(count($methodologies) > 0)
                                <select class="kt-select w-full" wire:model.live="selectedMethodologyId">
                                    <option value="">Select Methodology</option>
                                    @foreach($methodologies as $methodology)
                                        <option value="{{ $methodology->id }}">{{ $methodology->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <div class="p-3 bg-gray-50 border border-gray-200 rounded-md text-center">
                                    <i class="ki-filled ki-information text-gray-400 text-lg mb-1"></i>
                                    <p class="text-gray-600 text-sm">No active methodologies available</p>
                                    <p class="text-gray-500 text-xs">Contact an administrator to activate methodologies</p>
                                </div>
                            @endif
                            @error('selectedMethodologyId')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>

                        <!-- Pillar Selection -->
                        <div>
                            <label class="block text-sm font-medium mb-1">Pillar (Optional)</label>
                            <select class="kt-select w-full" wire:model.live="selectedPillarId" {{ !$selectedMethodologyId ? 'disabled' : '' }}>
                                <option value="">Direct to Methodology</option>
                                @foreach($pillars as $pillar)
                                    <option value="{{ $pillar->id }}">{{ $pillar->name }}</option>
                                @endforeach
                            </select>
                            @if(!$selectedMethodologyId)
                                <span class="text-gray-500 text-xs">Select a methodology first</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Module Selection -->
                @if($selectedMethodologyId)
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium">Select Module</h3>
                            <div class="kt-input max-w-48">
                                <i class="ki-filled ki-magnifier"></i>
                                <input type="text" class="kt-input" placeholder="Search modules..." wire:model.live="search" />
                            </div>
                        </div>

                        @if(count($availableModules) > 0)
                            <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg">
                                @foreach($availableModules as $module)
                                    <div class="border-b last:border-b-0 p-3 hover:bg-gray-50 cursor-pointer {{ $selectedModuleId == $module->id ? 'bg-primary/10 border-primary' : '' }}" 
                                         wire:click="selectModule({{ $module->id }})">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <h4 class="font-medium text-sm {{ $selectedModuleId == $module->id ? 'text-primary' : '' }}">
                                                    {{ $module->name }}
                                                </h4>
                                                @if($module->description)
                                                    <p class="text-gray-600 text-xs mt-1">
                                                        {{ Str::limit($module->description, 80) }}
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="ml-2">
                                                @if($selectedModuleId == $module->id)
                                                    <i class="ki-filled ki-check text-primary"></i>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('selectedModuleId')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <i class="ki-filled ki-element-4 text-4xl mb-2"></i>
                                <p>No available modules found for the selected criteria</p>
                                @if($search)
                                    <p class="text-sm">Try adjusting your search or selection</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>
            <div class="kt-modal-footer flex gap-2 justify-end p-5">
                <button type="button" class="kt-btn kt-btn-outline" wire:click="closeModal">Cancel</button>
                <button type="submit" class="kt-btn kt-btn-primary" {{ !$selectedModuleId ? 'disabled' : '' }}>Add Module</button>
            </div>
        </form>
    </div>
</div>