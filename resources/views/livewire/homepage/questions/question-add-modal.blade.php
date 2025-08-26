<div class="kt-modal hidden" data-kt-modal="true" id="question_add_modal" wire:ignore.self>
    <div class="kt-modal-content max-w-[600px] top-[15%]">
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">
                {{ $isEditMode ? 'Edit Question' : 'Add Question' }}
            </span>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" wire:click="closeModal">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form wire:submit.prevent="save">
            <div class="kt-modal-body p-5 flex flex-col gap-4">
                <!-- Question Title -->
                <div>
                    <label class="block text-sm font-medium mb-1">Question Title <span class="text-destructive">*</span></label>
                    <input type="text" class="kt-input w-full" wire:model.defer="title" placeholder="Enter question title" />
                </div>
                @error('title')<span class="text-destructive text-xs">{{ $message }}</span>@enderror

                <!-- Question Type -->
                <div>
                    <label class="block text-sm font-medium mb-1">Question Type <span class="text-destructive">*</span></label>
                    <select class="kt-input w-full" wire:model.live="type">
                        <option value="">Select question type</option>
                        @foreach($questionTypes as $type)
                            <option value="{{ $type['value'] }}" {{ $this->type === $type['value'] ? 'selected' : '' }}>
                                {{ $type['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('type')<span class="text-destructive text-xs">{{ $message }}</span>@enderror

                <!-- Predefined Answers (for non-MCQ questions) -->
                @if($this->type && !empty($predefinedAnswers) && count($predefinedAnswers) > 0)
                    <div>
                        <label class="block text-sm font-medium mb-1">Predefined Answers</label>
                        <div class="bg-gray-50 p-3 rounded border">
                            <div class="flex flex-wrap gap-2">
                                @foreach($predefinedAnswers as $answer)
                                    <span class="bg-primary/10 text-primary px-3 py-1 rounded-full text-sm">
                                        {{ $answer }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Custom Answers (for MCQ questions) -->
                @if($this->type && in_array($this->type, ['MCQSingle', 'MCQMultiple']))
                    <div>
                        <label class="block text-sm font-medium mb-1">Custom Answers <span class="text-destructive">*</span></label>
                        <div class="flex gap-2 mb-2">
                            <input
                                type="text"
                                class="kt-input flex-1"
                                wire:model.defer="newAnswer"
                                wire:keydown.enter.prevent="addAnswer"
                                placeholder="Type answer and press Enter"
                            />
                            <button
                                type="button"
                                class="kt-btn kt-btn-primary"
                                wire:click="addAnswer"
                            >
                                Add Answer
                            </button>
                        </div>

                        @if(count($customAnswers) > 0)
                            <div class="space-y-2">
                                @foreach($customAnswers as $index => $answer)
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="text"
                                            class="kt-input flex-1"
                                            wire:model="customAnswers.{{ $index }}"
                                            placeholder="Answer {{ $index + 1 }}"
                                        />
                                        @if(count($customAnswers) > 2)
                                            <button
                                                type="button"
                                                class="kt-btn kt-btn-outline kt-btn-sm text-destructive"
                                                wire:click="removeAnswer({{ $index }})"
                                                title="Remove answer"
                                            >
                                                <i class="ki-filled ki-cross"></i>
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @error('customAnswers')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                    @error('customAnswers.*')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                @endif

                <!-- Tags (Shared Component) -->
                <livewire:shared.components.tag-picker wire:model="tags" :label="'Tags'" :placeholder="'Search or add tags'" :addButtonText="'Add Tag'" />
                @error('tags')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                @error('tags.*')<span class="text-destructive text-xs">{{ $message }}</span>@enderror

                @if($error)
                    <div class="kt-error-message text-destructive bg-destructive/10 p-3 rounded">
                        {{ $error }}
                    </div>
                @endif

                <div class="flex justify-end mt-4">
                    <button type="button" class="kt-btn kt-btn-outline me-2" wire:click="closeModal">Cancel</button>
                    <button type="button" class="kt-btn kt-btn-outline me-2" wire:click="save(true)">Save and Add New</button>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        {{ $isEditMode ? 'Update Question' : 'Save Question' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
