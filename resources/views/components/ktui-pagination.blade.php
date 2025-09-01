@props(['paginator'])

<div class="flex justify-center mt-6 mb-2">
    <div class="kt-card kt-card-div">
        <div class="kt-card-content p-1">
            <ol class="kt-pagination">
                {{-- First Page --}}
                <li class="kt-pagination-item">
                    <button class="kt-btn kt-btn-icon kt-btn-ghost {{ $paginator->currentPage() <= 1 ? 'opacity-50 cursor-not-allowed' : '' }}" 
                       wire:click="{{ $paginator->currentPage() > 1 ? 'gotoPage(1)' : '' }}"
                       @if($paginator->currentPage() <= 1) disabled @endif>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-first rtl:rotate-180" aria-hidden="true">
                            <path d="m17 18-6-6 6-6"></path>
                            <path d="M7 6v12"></path>
                        </svg>
                    </button>
                </li>

                {{-- Previous Page --}}
                <li class="kt-pagination-item">
                    <button class="kt-btn kt-btn-icon kt-btn-ghost {{ !$paginator->previousPageUrl() ? 'opacity-50 cursor-not-allowed' : '' }}" 
                       wire:click="{{ $paginator->previousPageUrl() ? 'previousPage' : '' }}"
                       @if(!$paginator->previousPageUrl()) disabled @endif>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left rtl:rotate-180" aria-hidden="true">
                            <path d="m15 18-6-6 6-6"></path>
                        </svg>
                    </button>
                </li>

                {{-- Page Numbers --}}
                @php
                    $start = max(1, $paginator->currentPage() - 2);
                    $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
                @endphp

                @if($start > 1)
                    <li class="kt-pagination-item">
                        <button class="kt-btn kt-btn-icon kt-btn-ghost" wire:click="gotoPage(1)">1</button>
                    </li>
                    @if($start > 2)
                        <li class="kt-pagination-ellipsis">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ellipsis" aria-hidden="true">
                                <circle cx="12" cy="12" r="1"></circle>
                                <circle cx="19" cy="12" r="1"></circle>
                                <circle cx="5" cy="12" r="1"></circle>
                            </svg>
                        </li>
                    @endif
                @endif

                @for($page = $start; $page <= $end; $page++)
                    <li class="kt-pagination-item">
                        <button class="kt-btn kt-btn-icon kt-btn-ghost {{ $page == $paginator->currentPage() ? 'active' : '' }}" wire:click="gotoPage({{ $page }})">{{ $page }}</button>
                    </li>
                @endfor

                @if($end < $paginator->lastPage())
                    @if($end < $paginator->lastPage() - 1)
                        <li class="kt-pagination-ellipsis">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ellipsis" aria-hidden="true">
                                <circle cx="12" cy="12" r="1"></circle>
                                <circle cx="19" cy="12" r="1"></circle>
                                <circle cx="5" cy="12" r="1"></circle>
                            </svg>
                        </li>
                    @endif
                    <li class="kt-pagination-item">
                        <button class="kt-btn kt-btn-icon kt-btn-ghost" wire:click="gotoPage({{ $paginator->lastPage() }})">{{ $paginator->lastPage() }}</button>
                    </li>
                @endif

                {{-- Next Page --}}
                <li class="kt-pagination-item">
                    <button class="kt-btn kt-btn-icon kt-btn-ghost {{ !$paginator->nextPageUrl() ? 'opacity-50 cursor-not-allowed' : '' }}" 
                       wire:click="{{ $paginator->nextPageUrl() ? 'nextPage' : '' }}"
                       @if(!$paginator->nextPageUrl()) disabled @endif>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right rtl:rotate-180" aria-hidden="true">
                            <path d="m9 18 6-6-6-6"></path>
                        </svg>
                    </button>
                </li>

                {{-- Last Page --}}
                <li class="kt-pagination-item">
                    <button class="kt-btn kt-btn-icon kt-btn-ghost {{ $paginator->currentPage() >= $paginator->lastPage() ? 'opacity-50 cursor-not-allowed' : '' }}" 
                       wire:click="{{ $paginator->currentPage() < $paginator->lastPage() ? 'gotoPage(' . $paginator->lastPage() . ')' : '' }}"
                       @if($paginator->currentPage() >= $paginator->lastPage()) disabled @endif>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-last rtl:rotate-180" aria-hidden="true">
                            <path d="m7 18 6-6-6-6"></path>
                            <path d="M17 6v12"></path>
                        </svg>
                    </button>
                </li>
            </ol>
        </div>
    </div>
</div>
