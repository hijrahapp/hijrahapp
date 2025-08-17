<div class="kt-modal hidden" data-kt-modal="true" id="methodology_module_questions_modal" wire:ignore.self>
    <div class="kt-modal-content max-w-[800px] top-[10%] max-h-[96vh] overflow-y-auto">
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">Configure Questions</span>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" wire:click="$dispatch('click')">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body p-5 flex flex-col gap-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="kt-input">
                    <i class="ki-filled ki-magnifier"></i>
                    <input type="text" class="kt-input" placeholder="Search questions by title" wire:model.live="search" />
                </div>
                <div class="relative">
                    <div class="kt-input">
                        <i class="ki-filled ki-filter"></i>
                        <input type="text" class="kt-input" placeholder="Filter by tag" wire:model.live="tagSearch" />
                    </div>
                    @if($showTagSuggestions && count($tagSuggestions) > 0)
                        <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                            @foreach($tagSuggestions as $suggestion)
                                <button type="button" class="w-full text-left px-4 py-2 hover:bg-gray-100" wire:click="selectTagSuggestion('{{ $suggestion['title'] }}')">
                                    {{ $suggestion['title'] }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
                <select class="kt-select" wire:model.live="typeFilter">
                    <option value="">All Types</option>
                    @foreach($questionTypes as $type)
                        <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="border rounded p-4">
                <div class="font-medium mb-2">Select Questions</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($questionSuggestions as $q)
                        <label class="flex items-start gap-2">
                            <input type="checkbox" class="kt-checkbox"
                                   x-data="{ id: {{ $q->id }} }"
                                   x-init="window.addEventListener('revert-question-selection', e => { if (e.detail.id === id) { $el.checked = true; } })"
                                   wire:click="toggleQuestion({{ $q->id }})"
                                   @checked(in_array($q->id, $selectedQuestionIds, true)) />
                            <span>
                                <div class="font-medium">{{ $q->title }}</div>
                                <div class="text-xs text-secondary-foreground/70">
                                    Type:
                                    @if($q->type instanceof \App\Enums\QuestionType)
                                        {{ $q->type->getLabel() }}
                                    @else
                                        {{ ucfirst((string)$q->type) }}
                                    @endif
                                </div>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            @if(count($selectedQuestionIds) > 0)
                <div class="space-y-4" id="moduleSelectedQuestions">
                    @foreach($selectedQuestionIds as $qid)
                        @php
                            $question = \App\Models\Question::find($qid);
                            $answers = $question ? $question->answers : collect();
                        @endphp
                        <div class="kt-accordion" data-kt-accordion="true" data-question-id="{{ $qid }}">
                            <div class="kt-card kt-accordion-item" data-kt-accordion-item="true">
                                <span class="kt-accordion-toggle kt-card-header" data-kt-accordion-toggle="true" aria-controls="q_content_{{ $qid }}" id="q_toggle_{{ $qid }}">
                                    <h3 class="kt-card-title">{{ $sequences[$qid] ?? '' }}: {{ $question?->title }}</h3>
                                    <div class="flex items-center gap-2 ms-auto text-xs">
                                        <button type="button" class="kt-btn kt-btn-outline kt-btn-sm" wire:click.stop="moveQuestionUp({{ $qid }})" title="Move up">
                                            <i class="ki-filled ki-arrow-up"></i>
                                        </button>
                                        <button type="button" class="kt-btn kt-btn-outline kt-btn-sm" wire:click.stop="moveQuestionDown({{ $qid }})" title="Move down">
                                            <i class="ki-filled ki-arrow-down"></i>
                                        </button>
                                    </div>
                                    <span aria-hidden="true" class="kt-accordion-indicator">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus kt-accordion-indicator-on" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus kt-accordion-indicator-off" aria-hidden="true"><path d="M5 12h14"></path></svg>
                                    </span>
                                </span>
                                <div class="kt-accordion-content hidden p-3" aria-labelledby="q_toggle_{{ $qid }}" id="q_content_{{ $qid }}">
                                    <div class="kt-card-body space-y-3">
                                        <div class="flex gap-2 items-center justify-end mb-0.5">
                                            <span class="text-xs text-secondary-foreground/70">Weight:</span>
                                            <div class="kt-input w-20">
                                                <input type="text" class="kt-input" placeholder="Weight %" wire:model.defer="questionWeights.{{ $qid }}" />
                                                <i class="ki-filled ki-percentage"></i>
                                            </div>
                                        </div>
                                        @foreach($answers as $ans)
                                            <div class="grid grid-cols-1 {{ $moduleId ? 'md:grid-cols-3' : 'md:grid-cols-1' }} gap-2 items-start pb-2">
                                                <div class="md:col-span-1">
                                                    <div class="text-sm font-bold">{{ $ans->title }}</div>
                                                </div>
                                                <div class="md:col-span-1">
                                                    <span class="text-xs text-secondary-foreground/70">Weight:</span>
                                                    <div class="kt-input">
                                                        <i class="ki-filled ki-graph"></i>
                                                        <input type="text" class="kt-input" placeholder="Answer weight %" wire:model.defer="answerWeights.{{ $ans->id }}" />
                                                    </div>
                                                </div>
                                                @if($moduleId)
                                                    <div class="md:col-span-1">
                                                        <span class="text-xs text-secondary-foreground/70">Leads to:</span>
                                                        <select class="kt-select w-full" wire:model="answerDependencies.{{ $ans->id }}">
                                                            <option value="">No dependency</option>
                                                            @foreach($selectedQuestionIds as $dqId)
                                                                @if($dqId !== $qid)
                                                                    <option value="{{ $dqId }}">Leads to: {{ \App\Models\Question::find($dqId)?->title }}</option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="kt-modal-footer flex gap-2 justify-end p-5">
            <button type="button" class="kt-btn kt-btn-outline" wire:click="$dispatch('click')">Cancel</button>
            <button type="button" class="kt-btn kt-btn-primary" wire:click="save">Save</button>
        </div>
    </div>
</div>



