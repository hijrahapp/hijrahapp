<div class="kt-container-fixed kt-main-container" id="feedbackFormManagementContainer">
    <div class="flex flex-wrap items-center justify-between gap-2 pb-4">
        <div class="flex items-center gap-2">
            <button wire:click="backToList" class="kt-btn kt-btn-outline flex items-center justify-center">
                <i class="ki-filled ki-arrow-left"></i>
            </button>
            <h1 class="text-xl font-medium leading-none text-mono">
                Feedback Forms Management
            </h1>
        </div>
        <div class="ml-auto flex gap-2">
            <button 
                wire:click="saveForm" 
                wire:loading.attr="disabled" 
                wire:target="saveForm"
                class="kt-btn kt-btn-primary"
                @if(!$hasChanges) disabled @endif
            >
                <i class="ki-filled ki-check"></i>
                Save Form
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 pb-4">
        <!-- Form Details Section -->
        <div data-kt-accordion="true" class="kt-accordion" id="form_details_accordion">
            <div class="kt-card kt-accordion-item active" data-kt-accordion-item="true">
                <button
                    id="form_details_toggle"
                    data-kt-accordion-toggle="true"
                    aria-controls="form_details_content"
                    class="kt-accordion-toggle kt-card-header"
                >
                    <h3 class="kt-card-title">Form Details</h3>
                    <span aria-hidden="true" class="kt-accordion-indicator">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus kt-accordion-indicator-on" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus kt-accordion-indicator-off" aria-hidden="true"><path d="M5 12h14"></path></svg>
                    </span>
                </button>
                <div class="kt-accordion-content" aria-labelledby="form_details_toggle" id="form_details_content">
                    <div class="kt-card-body p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Form Title <span class="text-destructive">*</span></label>
                                <input type="text" class="kt-input w-full" wire:model.live="formTitle" placeholder="Enter form title" />
                                @error('formTitle')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Form Version <span class="text-destructive">*</span></label>
                                <input type="text" class="kt-input w-full" wire:model.live="formVersion" placeholder="Enter form version (e.g., 1.0)" />
                                @error('formVersion')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                <p class="text-xs text-muted-foreground mt-1">Version must be greater than the current version</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1">Description</label>
                            <textarea class="kt-textarea w-full" rows="3" wire:model.live="formDescription" placeholder="Enter form description (optional)"></textarea>
                            @error('formDescription')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Builder Section -->
        <div data-kt-accordion="true" class="kt-accordion">
            <div class="kt-card kt-accordion-item active" data-kt-accordion-item="true">
                <button
                    id="form_builder_toggle"
                    data-kt-accordion-toggle="true"
                    aria-controls="form_builder_content"
                    class="kt-accordion-toggle kt-card-header"
                >
                    <h3 class="kt-card-title">Form Builder</h3>
                    <span aria-hidden="true" class="kt-accordion-indicator">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus kt-accordion-indicator-on" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus kt-accordion-indicator-off" aria-hidden="true"><path d="M5 12h14"></path></svg>
                    </span>
                </button>
                <div class="kt-accordion-content" aria-labelledby="form_builder_toggle" id="form_builder_content">
                    <div class="kt-card-body p-5">
                        <!-- Question Builder -->
                        <div class="kt-card kt-card-accent p-4 mb-6">
                            <h4 class="text-lg font-medium mb-4">Add New Question</h4>
                            <div class="grid grid-cols-1 gap-4">
                                <!-- Question Text -->
                                <div>
                                    <label class="block text-sm font-medium mb-1">Question Text <span class="text-destructive">*</span></label>
                                    <textarea class="kt-textarea w-full" rows="2" wire:model="questionText" placeholder="Enter your question"></textarea>
                                    @error('questionText')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Question Type -->
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Question Type <span class="text-destructive">*</span></label>
                                        <select class="kt-select w-full" wire:model.live="questionType">
                                            <option value="">Select Question Type</option>
                                            <option value="rating">Rating</option>
                                            <option value="single_choice">Single Choice</option>
                                            <option value="multiple_choice">Multiple Choice</option>
                                            <option value="text">Text</option>
                                        </select>
                                        @error('questionType')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                    </div>

                                    <!-- Required Toggle -->
                                    <div class="flex items-center">
                                        <label class="flex items-center gap-2">
                                            <input type="checkbox" class="kt-checkbox" wire:model="questionRequired">
                                            <span class="text-sm font-medium">Required Question</span>
                                        </label>
                                    </div>
                                </div>


                                <!-- Choice Options (only shown when type is single_choice or multiple_choice) -->
                                @if(in_array($questionType, ['single_choice', 'multiple_choice']))
                                    <div>
                                        <livewire:shared.components.list-input
                                            :items="$questionOptions"
                                            label="Answer Options"
                                            placeholder="Enter answer option"
                                            addButtonText="Add Option"
                                            identifier="questionOptions"
                                            :reorderEnabled="true" />
                                        @error('questionOptions')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                    </div>
                                @endif

                                <!-- Text Max Length (only shown when type is text) -->
                                @if($questionType === 'text')
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Maximum Length</label>
                                        <input type="number" min="1" max="2000" class="kt-input w-full" wire:model="questionMaxLength" placeholder="500" />
                                        @error('questionMaxLength')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                                        <p class="text-xs text-muted-foreground mt-1">Maximum characters allowed (1-2000)</p>
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-center justify-end gap-2 mt-4">
                                <button class="kt-btn kt-btn-outline" wire:click="resetQuestionForm">Clear</button>
                                <button class="kt-btn kt-btn-primary" wire:click="addQuestion" wire:loading.attr="disabled" wire:target="addQuestion">
                                    <i class="ki-filled ki-plus"></i>
                                    Add Question
                                </button>
                            </div>
                        </div>

                        <!-- Questions List -->
                        <div>
                            <h4 class="text-lg font-medium mb-4">Form Questions ({{ count($questions ?? []) }})</h4>
                            @error('questions')<span class="text-destructive text-sm block mb-4">{{ $message }}</span>@enderror
                            @if(empty($questions))
                                <div class="text-center py-8 text-muted-foreground">
                                    <i class="ki-filled ki-message-question text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No questions added yet</p>
                                    <p class="text-sm">Use the Question Builder above to add questions to your form</p>
                                    <p class="text-xs text-orange-600 mt-2">Note: Exactly one rating question is required</p>
                                </div>
                            @else
                                <div class="space-y-3">
                                    @foreach($questions as $index => $question)
                                        <div class="kt-card kt-card-div">
                                            <div class="kt-card-body p-4">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div class="flex-1">
                                                        <div class="flex items-center gap-2 mb-2">
                                                            <span class="font-bold">{{ $index + 1 }}:</span>
                                                            <span class="font-medium">{{ $question['text'] }}</span>
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                                @if($question['type'] === 'rating')
                                                                bg-blue-50 text-blue-600
                                                                @elseif($question['type'] === 'text')
                                                                bg-gray-50 text-gray-600
                                                                @else
                                                                bg-green-50 text-green-600
                                                                @endif">
                                                                {{ ucfirst(str_replace('_', ' ', $question['type'])) }}
                                                            </span>
                                                            @if($question['required'])
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-destructive/5 text-destructive">
                                                                    Required
                                                                </span>
                                                            @endif
                                                        </div>

                                                        @if($question['type'] === 'rating')
                                                            <p class="text-sm text-gray-700">
                                                                Rating scale: {{ $question['min_value'] ?? 1 }} to {{ $question['max_value'] ?? 5 }}
                                                            </p>
                                                        @elseif(in_array($question['type'], ['single_choice', 'multiple_choice']) && !empty($question['options']))
                                                            <div class="text-sm text-gray-700">
                                                                <p class="font-medium">Options:</p>
                                                                <div class="flex flex-wrap gap-2 mt-1">
                                                                    @foreach($question['options'] as $option)
                                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                                                            <i class="ki-filled ki-check-circle text-xs mr-1"></i>
                                                                            {{ is_array($option) ? ($option['label'] ?? $option['value'] ?? '') : $option }}
                                                                        </span>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @elseif($question['type'] === 'text' && isset($question['max_length']))
                                                            <p class="text-sm text-gray-700">
                                                                Maximum length: {{ $question['max_length'] }} characters
                                                            </p>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <!-- Move Up Button -->
                                                        @if($index > 0)
                                                            <button class="kt-btn kt-btn-sm kt-btn-outline" wire:click="moveQuestionUp({{ $index }})" title="Move Up">
                                                                <i class="ki-filled ki-up"></i>
                                                            </button>
                                                        @else
                                                            <button class="kt-btn kt-btn-sm kt-btn-outline" disabled title="Move Up">
                                                                <i class="ki-filled ki-up"></i>
                                                            </button>
                                                        @endif

                                                        <!-- Move Down Button -->
                                                        @if($index < count($questions) - 1)
                                                            <button class="kt-btn kt-btn-sm kt-btn-outline" wire:click="moveQuestionDown({{ $index }})" title="Move Down">
                                                                <i class="ki-filled ki-down"></i>
                                                            </button>
                                                        @else
                                                            <button class="kt-btn kt-btn-sm kt-btn-outline" disabled title="Move Down">
                                                                <i class="ki-filled ki-down"></i>
                                                            </button>
                                                        @endif

                                                        <!-- Remove Button -->
                                                        <button class="kt-btn kt-btn-sm kt-btn-outline kt-btn-destructive" wire:click="removeQuestion({{ $index }})" title="Remove Question">
                                                            <i class="ki-filled ki-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>