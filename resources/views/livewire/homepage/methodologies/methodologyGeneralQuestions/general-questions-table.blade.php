<div>
    <div class="kt-card kt-card-grid kt-card-div h-full min-w-full">
        <div class="kt-card-header flex justify-between items-center">
            <h3 class="kt-card-title">General Questions</h3>
            <div class="flex gap-2 items-center">
                <div class="kt-input w-150">
                    <i class="ki-filled ki-magnifier"></i>
                    <input type="text" class="kt-input" placeholder="Search Questions" wire:model.live="search" />
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
                <button class="kt-btn kt-btn-outline flex items-center justify-center" title="Configure" wire:click="openConfig">
                    <i class="ki-filled ki-setting-4"></i>
                </button>
            </div>
        </div>
        <div class="kt-card-table">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table kt-table-border table-fixed w-full">
                    <thead>
                        <tr>
                            <th class="w-20 text-center">Order</th>
                            <th class="">Name</th>
                            <th class="">Type</th>
                            <th class="w-24 text-center">Weight</th>
                            <th class="w-20 text-center">Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($questions as $index => $question)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $question->title }}</td>
                                <td>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ __('lookups.'.$question->type->value) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php $pivot = \DB::table('methodology_question')->where('methodology_id', $methodologyId)->where('question_id', $question->id)->first(); @endphp
                                    <span class="font-medium">{{ (int)($pivot->weight ?? 0) }}%</span>
                                </td>
                                <td class="text-center">
                                    <button class="kt-btn kt-btn-outline kt-btn-sm kt-btn-destructive" wire:click="remove({{ $question->id }})" title="Remove">
                                        <i class="ki-filled ki-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No general questions configured.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


