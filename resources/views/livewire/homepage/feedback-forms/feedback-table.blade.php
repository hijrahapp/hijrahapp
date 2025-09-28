<div>
    <div class="kt-card kt-card-grid kt-card-div h-full min-w-full">
        <div class="kt-card-header flex justify-between items-center">
            <h3 class="kt-card-title">Program Feedback Overview</h3>
            <div class="flex gap-2 items-center">
                <div class="kt-input max-w-48">
                    <i class="ki-filled ki-magnifier"></i>
                    <input type="text" class="kt-input" placeholder="Search Programs" wire:model.live="search" />
                </div>
            </div>
        </div>
        <div class="kt-card-table">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table kt-table-border table-fixed w-full">
                    <thead>
                        <tr>
                            <th class="w-20 text-center">#</th>
                            <th class="">Program Name</th>
                            <th class="">Description</th>
                            <th class="text-center">Total Submissions</th>
                            <th class="text-center">Average Rating</th>
                            <th class="text-center">Status</th>
                            <th class="w-20 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($programs as $index => $program)
                            <tr>
                                <td class="text-center">{{ $programs->firstItem() + $index }}</td>
                                <td>
                                    <div class="font-medium">{{ $program->name }}</div>
                                </td>
                                <td>
                                    <div class="max-w-xs truncate" title="{{ $program->description }}">
                                        {{ Str::limit($program->description, 100) }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                        @if($program->feedback_count > 0) bg-blue-50 text-blue-600 @else bg-gray-50 text-gray-600 @endif">
                                        {{ $program->feedback_count }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $averageRating = $program->calculated_avg_rating ?? 0;
                                    @endphp
                                    @if($averageRating > 0)
                                        <div class="flex items-center justify-center gap-1">
                                            <span class="font-medium">
                                                {{ $averageRating }}
                                            </span>
                                            <div class="flex">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="ki-filled ki-star text-xs 
                                                        @if($i <= $averageRating) text-yellow-500
                                                        @else text-gray-300
                                                        @endif">
                                                    </i>
                                                @endfor
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">No ratings</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($program->active)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-50 text-green-600">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-600">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="flex gap-2 justify-center">
                                        <button class="kt-btn kt-btn-outline flex items-center justify-center" wire:click="viewFeedback({{ $program->id }})">
                                            <i class="ki-filled ki-eye text-secondary-foreground"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8">
                                    <div class="flex flex-col items-center gap-3">
                                        <i class="ki-filled ki-file-down text-4xl text-gray-400"></i>
                                        <div>
                                            <p class="text-lg font-medium text-gray-600">No programs found</p>
                                            <p class="text-sm text-gray-500">There are no programs to display feedback for.</p>
                                        </div>
                                    </div>
                                </td>
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
                    Showing {{ $programs->firstItem() ?? 0 }} to {{ $programs->lastItem() ?? 0 }} of {{ $programs->total() ?? 0 }} Programs
                </span>
            </div>
        </div>
    </div>

    {{-- Pagination outside the table card --}}
    <x-ktui-pagination :paginator="$programs" />
</div>
