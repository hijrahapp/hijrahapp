<div>
    <div class="kt-card kt-card-grid kt-card-div h-full min-w-full">
        <div class="kt-card-header flex justify-between items-center">
            <h3 class="kt-card-title">Enrichments</h3>
            <div class="flex gap-2 items-center">
                <div class="kt-input max-w-48">
                    <i class="ki-filled ki-magnifier"></i>
                    <input type="text" class="kt-input" placeholder="Search Enrichments" wire:model.live="search" />
                </div>
                <div class="relative">
                    <div class="kt-input">
                        <i class="ki-filled ki-filter"></i>
                        <select wire:model.live="typeFilter">
                            <option value=''>All Types</option>
                            @foreach($this->enrichmentTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button class="kt-btn kt-btn-outline flex items-center justify-center" data-kt-modal-toggle="#enrichment_add_modal" title="Add Enrichment">
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
                            <th class="text-center">Type</th>
                            <th class="">Categories</th>
                            <th class="">Interests</th>
                            <th class="">Tags</th>
                            <th class="text-center">Status</th>
                            <th class="w-20 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($enrichments as $index => $enrichment)
                            <tr wire:key="enrichment-{{ $enrichment->id }}">
                                <td class="text-center">{{ $enrichments->firstItem() + $index }}</td>
                                <td>
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $enrichment->title }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-600">
                                        {{ ucfirst($enrichment->type) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $categoryData = $this->getCategoryTitles($enrichment->categories);
                                    @endphp
                                    @if(count($categoryData['categories']) > 0)
                                        <div class="flex flex-wrap gap-1 items-center">
                                            @foreach($categoryData['categories'] as $categoryName)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary border border-primary/20">
                                                    {{ $categoryName }}
                                                </span>
                                            @endforeach
                                            @if($categoryData['hasMore'])
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200" title="+{{ $categoryData['remainingCount'] }} more">
                                                    +{{ $categoryData['remainingCount'] }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">No categories</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $interestData = $this->getInterestTitles($enrichment->interests);
                                    @endphp
                                    @if(count($interestData['interests']) > 0)
                                        <div class="flex flex-wrap gap-1 items-center">
                                            @foreach($interestData['interests'] as $interestName)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary border border-primary/20">
                                                    {{ $interestName }}
                                                </span>
                                            @endforeach
                                            @if($interestData['hasMore'])
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200" title="+{{ $interestData['remainingCount'] }} more">
                                                    +{{ $interestData['remainingCount'] }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">No interests</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $tagData = $this->getTagTitles($enrichment->tags);
                                    @endphp
                                    @if(count($tagData['tags']) > 0)
                                        <div class="flex flex-wrap gap-1 items-center">
                                            @foreach($tagData['tags'] as $tagTitle)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary border border-primary/20">
                                                    {{ $tagTitle }}
                                                </span>
                                            @endforeach
                                            @if($tagData['hasMore'])
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200" title="+{{ $tagData['remainingCount'] }} more">
                                                    +{{ $tagData['remainingCount'] }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">No tags</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($enrichment->active)
                                        <button class="kt-btn kt-btn-outline kt-btn-sm kt-btn-destructive" x-on:click="$wire.call('openEnrichmentStatusModal', {{ Js::from(['id' => $enrichment->id, 'active' => false]) }})" title="Deactivate Enrichment">
                                            Deactivate
                                        </button>
                                    @else
                                        <button class="kt-btn kt-btn-outline kt-btn-sm kt-btn-primary" x-on:click="$wire.call('openEnrichmentStatusModal', {{ Js::from(['id' => $enrichment->id, 'active' => true]) }})" title="Activate Enrichment">
                                            Activate
                                        </button>
                                    @endif
                                </td>
                                <td class="text-center" wire:ignore>
                                    <div data-kt-dropdown="true" data-kt-dropdown-trigger="click" wire:key="dropdown-{{ $enrichment->id }}">
                                        <button class="kt-btn kt-btn-outline" data-kt-dropdown-toggle="true">
                                            <i class="ki-filled ki-dots-horizontal text-secondary-foreground"></i>
                                        </button>
                                        <div class="kt-dropdown-menu" data-kt-dropdown-menu="true">
                                            <ul class="kt-dropdown-menu-sub">
                                                <li>
                                                    <a href="{{ route('enrichment.manage', ['enrichmentId' => $enrichment->id]) }}" class="kt-dropdown-menu-link" data-kt-dropdown-dismiss="true">
                                                        <i class="ki-filled ki-setting-2"></i>
                                                        Manage
                                                    </a>
                                                </li>

                                                <li class="kt-dropdown-menu-separator"></li>

                                                <li>
                                                    <a href="#" class="kt-dropdown-menu-link text-danger" data-kt-dropdown-dismiss="true" wire:click="openDeleteEnrichmentModal({{ Js::from(['id' => $enrichment->id]) }})">
                                                        <i class="ki-filled ki-trash"></i>
                                                        Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">No Enrichments found.</td>
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
                    Showing {{ $enrichments->firstItem() ?? 0 }} to {{ $enrichments->lastItem() ?? 0 }} of {{ $enrichments->total() ?? 0 }} Enrichments
                </span>
            </div>
        </div>
    </div>

    <x-ktui-pagination :paginator="$enrichments" />
</div>
