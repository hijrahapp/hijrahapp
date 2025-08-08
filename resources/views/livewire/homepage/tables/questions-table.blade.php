<div class="kt-card kt-card-grid kt-card-div h-full min-w-full">
    <div class="kt-card-header flex justify-between items-center">
        <h3 class="kt-card-title">Questions</h3>
        <div class="flex gap-2 items-center">
            <div class="kt-input max-w-48">
                <i class="ki-filled ki-magnifier"></i>
                <input type="text" class="kt-input" placeholder="Search Questions" wire:input="setSearchProperty($event.target.value)" />
            </div>
            <button class="kt-btn kt-btn-outline flex items-center justify-center" data-kt-modal-toggle="#question_add_modal" title="Add Question">
                <i class="ki-filled ki-plus"></i>
            </button>
        </div>
    </div>
    <div class="kt-card-table">
        <div class="kt-scrollable-x-auto">
            <table class="kt-table kt-table-border table-fixed w-full">
                <thead>
                    <tr>
                        <th class="w-20 text-center">#</th>
                        <th class="">Title</th>
                        <th class="">Type</th>
                        <th class="">Tags</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($questions as $index => $question)
                        <tr>
                            <td class="text-center">{{ $questions->firstItem() + $index }}</td>
                            <td>{{ $question->title }}</td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $question->type }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $tagData = $this->getTagTitles($question->tags);
                                @endphp
                                @if(count($tagData['tags']) > 0)
                                    <div class="flex flex-wrap gap-1 items-center">
                                        @foreach($tagData['tags'] as $tagTitle)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary border border-primary/20">
                                                {{ $tagTitle }}
                                            </span>
                                        @endforeach
                                        @if($tagData['hasMore'])
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200" title="+{{ $tagData['remainingCount'] }} more tags">
                                                +{{ $tagData['remainingCount'] }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400 text-sm">No tags</span>
                                @endif
                            </td>
                            <td class="text-center flex gap-2 justify-center">
                                <button
                                    class="kt-btn kt-btn-outline flex items-center justify-center"
                                    wire:click="editQuestion({{ $question->id }})"
                                    title="Edit Question">
                                    <i class="ki-filled ki-pencil text-secondary-foreground"></i>
                                </button>
                                <button
                                    class="kt-btn kt-btn-outline flex items-center justify-center"
                                    wire:click="openDeleteQuestionModal({{ Js::from(['id' => $question->id]) }})"
                                    title="Delete Question">
                                    <i class="ki-filled ki-trash text-destructive"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No Questions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="kt-card-footer flex-col justify-center gap-5 text-sm font-medium text-secondary-foreground md:flex-row md:justify-between">
        <div class="order-2 flex items-center gap-2 md:order-1">
        </div>
        <div class="order-1 flex items-center gap-4 md:order-2">
            <span>
                Showing {{ $questions->firstItem() ?? 0 }} to {{ $questions->lastItem() ?? 0 }} of {{ $questions->total() ?? 0 }} Questions
            </span>
            <div>
                {{ $questions->links() }}
            </div>
        </div>
    </div>
</div>
