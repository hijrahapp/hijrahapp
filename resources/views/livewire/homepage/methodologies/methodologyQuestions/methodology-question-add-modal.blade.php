<div class="kt-modal hidden" data-kt-modal="true" id="methodology_question_add_modal" wire:ignore.self>
    <div class="kt-modal-content max-w-[800px] top-[10%] max-h-[96vh] overflow-y-auto">
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">
                {{ $isEditMode ? 'Edit Question' : 'Add Question to Methodology' }}
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

                <!-- Question Selection -->
                <div>
                    <h3 class="text-lg font-medium mb-4">Question Selection</h3>

                    @if(!$isEditMode)
                        <div>
                            <label class="block text-sm font-medium mb-1">Search Questions <span class="text-destructive">*</span></label>
                            <input type="text" class="kt-input w-full" placeholder="Search questions..." wire:model.live="selectedQuestionTitle" />

                            @if(!empty($selectedQuestionTitle) && !$isEditMode)
                                <div class="mt-2 max-h-60 overflow-y-auto border border-gray-300 rounded-md">
                                    @foreach($availableQuestions as $question)
                                        @if(stripos($question['title'], $selectedQuestionTitle) !== false)
                                            <button type="button" class="w-full text-left px-4 py-3 hover:bg-gray-100 border-b border-gray-200 last:border-b-0" wire:click="selectQuestion({{ $question['id'] }})">
                                                <div class="font-medium">{{ $question['title'] }}</div>
                                                <div class="text-sm text-gray-500">{{ $question['type'] }}</div>
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($selectedQuestionTitle)
                        <div class="mt-4 bg-gray-50 p-4 rounded-lg">
                            <h5 class="font-medium mb-2">Selected Question:</h5>
                            <p class="text-sm">{{ $selectedQuestionTitle }}</p>
                            <p class="text-xs text-gray-500 mt-1">Type: {{ $selectedQuestionType }}</p>

                            @if(!empty($selectedQuestionAnswers))
                                <div class="mt-3">
                                    <h6 class="font-medium text-sm mb-2">Answers:</h6>
                                    <div class="space-y-1">
                                        @foreach($selectedQuestionAnswers as $answer)
                                            <div class="text-sm">{{ $answer }}</div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                @if($selectedQuestionTitle)
                    <div class="kt-separator mt-4"></div>

                    <!-- Configuration -->
                    <div>
                        <h3 class="text-lg font-medium mb-4">Configuration</h3>

                        <!-- Question Weight -->
                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1">Question Weight (%) <span class="text-destructive">*</span></label>
                            <input type="number" class="kt-input w-full" placeholder="Enter weight"
                                   wire:model="questionWeights.{{ $editingQuestionId ?: 'new' }}"
                                   min="0" max="100" step="0.01" />
                            <p class="text-xs text-gray-500 mt-1">Weight within the pillar/module (must be 100%)</p>
                        </div>

                        <!-- Pillar/Module Assignment -->
                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1">Assign to:</label>
                            <div class="space-y-2">
                                @if($methodology->type !== 'simple')
                                    <div>
                                        <label class="flex items-center">
                                            <input type="radio" class="kt-radio" wire:model="selectedPillarId" value="0" />
                                            <span class="ml-2 text-sm">General (No pillar/module)</span>
                                        </label>
                                    </div>
                                    @foreach($methodology->pillars as $pillar)
                                        <div>
                                            <label class="flex items-center">
                                                <input type="radio" class="kt-radio" wire:model="selectedPillarId" value="{{ $pillar->id }}" />
                                                <span class="ml-2 text-sm">Pillar: {{ $pillar->name }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                @else
                                    <div>
                                        <label class="flex items-center">
                                            <input type="radio" class="kt-radio" wire:model="selectedModuleId" value="0" />
                                            <span class="ml-2 text-sm">General (No module)</span>
                                        </label>
                                    </div>
                                    @foreach($methodology->modules as $module)
                                        <div>
                                            <label class="flex items-center">
                                                <input type="radio" class="kt-radio" wire:model="selectedModuleId" value="{{ $module->id }}" />
                                                <span class="ml-2 text-sm">Module: {{ $module->name }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Answer Weights -->
                        @if(!empty($selectedQuestionAnswers))
                            <div class="mt-4">
                                <label class="block text-sm font-medium mb-1">Answer Weights (%) <span class="text-destructive">*</span></label>
                                <div class="space-y-2">
                                    @php
                                        $question = \App\Models\Question::where('title', $selectedQuestionTitle)->with('answers')->first();
                                    @endphp
                                    @if($question)
                                        @foreach($question->answers as $answer)
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm flex-1">{{ $answer->title }}</span>
                                                <input type="number" class="kt-input w-20" placeholder="0"
                                                       wire:model="answerWeights.{{ $answer->id }}"
                                                       min="0" max="100" step="0.01" />
                                                <span class="text-xs text-gray-500">%</span>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Weights must sum to 100%</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="kt-modal-footer py-4 px-5 flex justify-end gap-2">
                <button type="button" class="kt-btn kt-btn-outline" wire:click="closeModal">Cancel</button>
                <button type="submit" class="kt-btn kt-btn-primary">
                    {{ $isEditMode ? 'Update Question' : 'Add Question' }}
                </button>
            </div>
        </form>
    </div>
</div>
