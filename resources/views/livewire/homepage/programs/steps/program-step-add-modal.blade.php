<div class="kt-modal hidden" data-kt-modal="true" id="program_step_add_modal" wire:ignore.self>
    <div class="kt-modal-content max-w-[800px] top-[5%] max-h-[96vh] overflow-y-auto">
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">
                {{ $isEditMode ? 'Edit Step' : 'Add Step' }}
            </span>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" wire:click="resetForm" data-kt-modal-dismiss="true">
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Name <span class="text-destructive">*</span></label>
                            <input type="text" class="kt-input w-full" wire:model.defer="name" placeholder="Enter step name" />
                            @error('name')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Type <span class="text-destructive">*</span></label>
                            <select class="kt-select w-full" wire:model.live="type" @if($isEditMode) disabled @endif>
                                <option value="">Select Type</option>
                                @foreach($stepTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Duration <span class="text-destructive">*</span></label>
                            <input type="number" min="1" class="kt-input w-full" wire:model.defer="timeToFinish" placeholder="Enter duration" />
                            @error('timeToFinish')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Time Unit <span class="text-destructive">*</span></label>
                            <select class="kt-select w-full" wire:model.defer="timeType">
                                @foreach($timeTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('timeType')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>

                    </div>
                </div>

                <!-- Type-specific fields -->
                @if($type)
                    <div>
                        <h3 class="text-lg font-medium mb-4">{{ $stepTypes[$type] ?? $type }} Details</h3>

                        @if($type === 'journal')
                            <div>
                                <livewire:shared.components.textarea :label="'Header'" :required="true" wire:model.defer="header" :placeholder="'Enter journal header'" :rows="3" />
                                @error('header')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>
                        @elseif($type === 'article')
                            <div class="space-y-4">
                                <div>
                                    <livewire:shared.components.textarea :label="'Header'" :required="true" wire:model.defer="header" :placeholder="'Enter article header'" :rows="2" />
                                    @error('header')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <livewire:shared.components.textarea :label="'Content'" :required="true" wire:model.defer="content" :placeholder="'Enter article content'" :rows="5" />
                                    @error('content')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        @elseif($type === 'advice')
                            <div class="space-y-4">
                                <div>
                                    <livewire:shared.components.textarea :label="'Header'" :required="true" wire:model.defer="header" :placeholder="'Enter advice header'" :rows="2" />
                                    @error('header')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <livewire:shared.components.list-input
                                        :items="$advices"
                                        label="Advices"
                                        :required="true"
                                        placeholder="Enter advice"
                                        addButtonText="Add Advice"
                                        identifier="advices"
                                        wire:key="advices-list"
                                    />
                                    @error('advices')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        @elseif($type === 'daily_mission')
                            <div class="space-y-4">
                                <div>
                                    <livewire:shared.components.textarea :label="'Header'" :required="true" wire:model.defer="header" :placeholder="'Enter mission header'" :rows="2" />
                                    @error('header')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <livewire:shared.components.textarea :label="'Content'" :required="true" wire:model.defer="content" :placeholder="'Enter mission content'" :rows="4" />
                                    @error('content')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        @elseif($type === 'quiz')
                            <div>
                                <p class="text-sm text-gray-600 mb-4">Quiz questions will be managed separately after creating the step.</p>
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <i class="ki-filled ki-information text-blue-500 mr-2"></i>
                                    <span class="text-sm">Questions can be added and managed using the "Manage Questions" action in the steps table.</span>
                                </div>
                            </div>
                        @elseif(in_array($type, ['video', 'audio']))
                            <div class="space-y-4">
                                <div>
                                    <livewire:shared.components.file-picker 
                                        :label="'Content File'" 
                                        :required="true"
                                        :placeholder="'Enter ' . $type . ' URL or upload a file'"
                                        wire:model="contentUrl" 
                                        :allowedTypes="$type === 'video' ? ['mp4', 'mov', 'avi'] : ['mp3', 'wav', 'aac']"
                                        :maxSize="50"
                                        :helpText="'Upload a ' . $type . ' file or provide a URL'"
                                        wire:key="content-file-picker-{{ $type }}-{{ $stepId ?? 'new' }}"
                                    />
                                    @error('contentUrl')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <livewire:shared.components.textarea :label="'Description (Optional)'" wire:model.defer="description" :placeholder="'Enter description'" :rows="3" />
                                    @error('description')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        @elseif($type === 'book')
                            <div class="space-y-4">
                                <div>
                                    <livewire:shared.components.file-picker 
                                        :label="'Book File'" 
                                        :required="true"
                                        :placeholder="'Enter book URL or upload a PDF file'"
                                        wire:model="contentUrl" 
                                        :allowedTypes="['pdf']"
                                        :maxSize="10"
                                        :helpText="'Upload a PDF file or provide a URL ending with .pdf'"
                                        wire:key="book-file-picker-{{ $stepId ?? 'new' }}"
                                    />
                                    @error('contentUrl')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <livewire:shared.components.image-picker 
                                        :label="'Cover Image'" 
                                        wire:model="contentImage" 
                                        :required="true"
                                        wire:key="content-image-picker"
                                    />
                                    @error('contentImage')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <livewire:shared.components.textarea :label="'Description (Optional)'" wire:model.defer="description" :placeholder="'Enter book description'" :rows="3" />
                                    @error('description')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        @elseif($type === 'challenge')
                            <div class="space-y-4">
                                <div>
                                    <livewire:shared.components.textarea :label="'Header'" :required="true" wire:model.defer="header" :placeholder="'Enter challenge header'" :rows="2" />
                                    @error('header')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <livewire:shared.components.list-input
                                        :items="$challenges"
                                        label="Challenges"
                                        :required="true"
                                        placeholder="Enter challenge"
                                        addButtonText="Add Challenge"
                                        identifier="challenges"
                                        wire:key="challenges-list"
                                    />
                                    @error('challenges')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="kt-modal-footer flex gap-2 justify-end p-5">
                <button type="button" class="kt-btn kt-btn-outline" wire:click="resetForm" data-kt-modal-dismiss="true">Cancel</button>
                <button type="submit" class="kt-btn kt-btn-primary">{{ $isEditMode ? 'Update Step' : 'Create Step' }}</button>
            </div>
        </form>
    </div>
</div>
