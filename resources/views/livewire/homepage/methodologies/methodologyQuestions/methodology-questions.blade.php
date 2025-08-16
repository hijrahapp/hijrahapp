<div>
    <div class="kt-container-fixed kt-main-container h-full min-w-full">
        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-5 pb-7.5 lg:items-end">
            <div class="flex flex-col justify-center gap-2">
                <div class="flex items-center gap-2">
                    <a href="{{ route('homepage.index') }}" class="kt-btn kt-btn-outline kt-btn-sm">
                        <i class="ki-filled ki-arrow-left"></i>
                        Back to Methodologies
                    </a>
                </div>
                <h1 class="text-xl font-medium leading-none text-mono">
                    {{ $methodology->name }} - Questions Management
                </h1>
                <p class="text-sm text-secondary-foreground">
                    Manage questions for this methodology
                </p>
            </div>
            <div class="flex gap-2 items-center">
                            <button class="kt-btn kt-btn-primary" data-kt-modal-toggle="#methodology_question_add_modal">
                <i class="ki-filled ki-plus"></i>
                Add Question
            </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="kt-card kt-card-grid kt-card-div mb-5">
            <div class="kt-card-header">
                <div class="flex gap-2 items-center">
                    <div class="kt-input max-w-48">
                        <i class="ki-filled ki-magnifier"></i>
                        <input type="text" class="kt-input" placeholder="Search Questions" wire:model.live="search" />
                    </div>
                    <div class="relative max-w-48">
                        <select class="kt-input" wire:model.live="typeFilter">
                            <option value="">All Types</option>
                            @foreach($questionTypes as $type)
                                <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative max-w-64 min-w-56">
                        <div class="kt-input">
                            <i class="ki-filled ki-filter"></i>
                            <input type="text" class="kt-input" placeholder="Filter by tag" wire:model.live="tagSearch" />
                            @if($tagFilter)
                                <button type="button" class="absolute end-2 top-1/2 -translate-y-1/2 text-muted-foreground" title="Clear" wire:click="clearTagFilter">
                                    <i class="ki-filled ki-cross"></i>
                                </button>
                            @endif
                        </div>
                        @if($showTagSuggestions && count($tagSuggestions) > 0)
                            <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                                @foreach($tagSuggestions as $suggestion)
                                    <button type="button" class="w-full text-left px-4 py-2 hover:bg-gray-100" wire:click="selectTagFilter({{ $suggestion['id'] }}, '{{ $suggestion['title'] }}')">
                                        {{ $suggestion['title'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions Table -->
        <div class="kt-card kt-card-grid kt-card-div h-full min-w-full">
            <div class="kt-card-table">
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table kt-table-border table-fixed w-full" id="questionsTable">
                        <thead>
                            <tr>
                                <th class="w-20 text-center">#</th>
                                <th class="w-10 text-center">Order</th>
                                <th class="">Question</th>
                                <th class="text-center">Type</th>
                                <th class="text-center">Answers</th>
                                <th class="text-center">Pillar/Module</th>
                                <th class="text-center">Weight</th>
                                <th class="text-center">Status</th>
                                <th class="w-20 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="questionsTableBody">
                            @forelse($methodologyQuestions as $index => $question)
                                <tr data-question-id="{{ $question->id }}" class="cursor-move">
                                    <td class="text-center">{{ $methodologyQuestions->firstItem() + $index }}</td>
                                    <td class="text-center">
                                        <i class="ki-filled ki-arrows-move text-gray-400"></i>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{ $question->title }}</span>
                                            @php $tagInfo = $this->getTagTitles($question->tags, 2); @endphp
                                            @if($tagInfo['tags'])
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    @foreach($tagInfo['tags'] as $tag)
                                                        <span class="kt-badge kt-badge-sm kt-badge-outline">{{ $tag }}</span>
                                                    @endforeach
                                                    @if($tagInfo['hasMore'])
                                                        <span class="kt-badge kt-badge-sm kt-badge-outline">+{{ $tagInfo['remainingCount'] }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            @if($question->type == 'YesNo') bg-green-50 text-green-600
                                            @elseif($question->type == 'TrueFalse') bg-blue-50 text-blue-600
                                            @elseif($question->type == 'MCQSingle') bg-purple-50 text-purple-600
                                            @elseif($question->type == 'MCQMultiple') bg-orange-50 text-orange-600
                                            @elseif($question->type == 'Rating1to5') bg-red-50 text-red-600
                                            @elseif($question->type == 'Rating1to10') bg-indigo-50 text-indigo-600
                                            @else bg-gray-50 text-gray-600 @endif">
                                            {{ $question->type->getLabel() }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="flex flex-col gap-1">
                                            @foreach($question->answers->take(3) as $answer)
                                                <span class="text-sm">{{ $answer->title }}</span>
                                            @endforeach
                                            @if($question->answers->count() > 3)
                                                <span class="text-xs text-gray-500">+{{ $question->answers->count() - 3 }} more</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $pillar = $question->pillars()->where('methodology_id', $methodologyId)->first();
                                            $module = $question->modules()->where('methodology_id', $methodologyId)->first();
                                        @endphp
                                        @if($pillar)
                                            <span class="kt-badge kt-badge-sm kt-badge-primary">Pillar: {{ $pillar->name }}</span>
                                        @elseif($module)
                                            <span class="kt-badge kt-badge-sm kt-badge-secondary">Module: {{ $module->name }}</span>
                                        @else
                                            <span class="text-gray-400 text-sm">General</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="font-medium">{{ $question->pivot->weight ?? 1.0 }}%</span>
                                    </td>
                                    <td class="text-center">
                                        @if($question->active)
                                            <span class="kt-badge kt-badge-sm kt-badge-success">Active</span>
                                        @else
                                            <span class="kt-badge kt-badge-sm kt-badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-center" wire:ignore>
                                        <div data-kt-dropdown="true" data-kt-dropdown-trigger="click">
                                            <button class="kt-btn kt-btn-outline" data-kt-dropdown-toggle="true">
                                                <i class="ki-filled ki-dots-horizontal text-secondary-foreground"></i>
                                            </button>
                                            <div class="kt-dropdown-menu w-52" data-kt-dropdown-menu="true">
                                                <ul class="kt-dropdown-menu-sub">
                                                    <li>
                                                        <a href="#" class="kt-dropdown-menu-link" data-kt-dropdown-dismiss="true" wire:click="setEditingQuestion({{ $question->id }})" data-kt-modal-toggle="#methodology_question_add_modal">
                                                            <i class="ki-filled ki-pencil"></i>
                                                            Edit Question
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#" class="kt-dropdown-menu-link text-danger" data-kt-dropdown-dismiss="true" wire:click="openDeleteModal({{ $question->id }})">
                                                            <i class="ki-filled ki-trash"></i>
                                                            Delete Question
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">No questions found for this methodology.</td>
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
                        Showing {{ $methodologyQuestions->firstItem() ?? 0 }} to {{ $methodologyQuestions->lastItem() ?? 0 }} of {{ $methodologyQuestions->total() ?? 0 }} Questions
                    </span>
                </div>
            </div>
        </div>

        {{-- Pagination outside the table card --}}
        <x-ktui-pagination :paginator="$methodologyQuestions" />
    </div>

    <!-- Add/Edit Question Modal -->
    <livewire:homepage.methodologies.methodologyQuestions.methodology-question-add-modal :methodologyId="$methodologyId" :editingQuestionId="$editingQuestionId" />

    <!-- Drag and Drop JavaScript -->
    <script>
        document.addEventListener('livewire:init', () => {
            let draggedElement = null;
            let placeholder = null;

            function createPlaceholder() {
                const tr = document.createElement('tr');
                tr.className = 'bg-gray-100 border-2 border-dashed border-gray-300';
                tr.innerHTML = '<td colspan="9" class="text-center py-4 text-gray-500">Drop here</td>';
                return tr;
            }

            function initDragAndDrop() {
                const tbody = document.getElementById('questionsTableBody');
                if (!tbody) return;

                const rows = tbody.querySelectorAll('tr[data-question-id]');

                rows.forEach(row => {
                    row.setAttribute('draggable', true);

                    row.addEventListener('dragstart', (e) => {
                        draggedElement = row;
                        row.style.opacity = '0.5';
                        e.dataTransfer.effectAllowed = 'move';
                    });

                    row.addEventListener('dragend', () => {
                        row.style.opacity = '';
                        if (placeholder) {
                            placeholder.remove();
                            placeholder = null;
                        }
                    });

                    row.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        e.dataTransfer.dropEffect = 'move';

                        if (draggedElement === row) return;

                        const rect = row.getBoundingClientRect();
                        const midpoint = rect.top + rect.height / 2;

                        if (e.clientY < midpoint) {
                            row.parentNode.insertBefore(placeholder || createPlaceholder(), row);
                        } else {
                            row.parentNode.insertBefore(placeholder || createPlaceholder(), row.nextSibling);
                        }
                    });

                    row.addEventListener('drop', (e) => {
                        e.preventDefault();

                        if (draggedElement === row) return;

                        const newOrder = Array.from(tbody.querySelectorAll('tr[data-question-id]'))
                            .map(tr => parseInt(tr.getAttribute('data-question-id')));

                        // Send the new order to Livewire
                        @this.reorderQuestions(newOrder);
                    });
                });
            }

            // Initialize drag and drop when the component loads
            initDragAndDrop();

            // Re-initialize after Livewire updates
            Livewire.hook('morph.updated', () => {
                setTimeout(initDragAndDrop, 100);
            });
        });
    </script>
</div>
