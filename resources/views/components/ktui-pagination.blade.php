@props(['paginator'])

<div class="flex justify-center mt-6">
    <div class="kt-card kt-card-div">
        <div class="kt-card-content p-1">
            <ol class="kt-pagination">
                {{-- First Page --}}
                <li class="kt-pagination-item">
                    <a class="kt-btn kt-btn-icon kt-btn-ghost {{ $paginator->currentPage() <= 1 ? 'opacity-50 cursor-not-allowed' : '' }}" 
                       href="{{ $paginator->url(1) }}" 
                       wire:click.prevent="{{ $paginator->currentPage() > 1 ? 'gotoPage(1)' : '' }}"
                       @if($paginator->currentPage() <= 1) style="pointer-events: none;" @endif>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-first rtl:rotate-180" aria-hidden="true">
                            <path d="m17 18-6-6 6-6"></path>
                            <path d="M7 6v12"></path>
                        </svg>
                    </a>
                </li>

                {{-- Previous Page --}}
                <li class="kt-pagination-item">
                    <a class="kt-btn kt-btn-icon kt-btn-ghost {{ !$paginator->previousPageUrl() ? 'opacity-50 cursor-not-allowed' : '' }}" 
                       href="{{ $paginator->previousPageUrl() }}" 
                       wire:click.prevent="{{ $paginator->previousPageUrl() ? 'previousPage' : '' }}"
                       @if(!$paginator->previousPageUrl()) style="pointer-events: none;" @endif>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left rtl:rotate-180" aria-hidden="true">
                            <path d="m15 18-6-6 6-6"></path>
                        </svg>
                    </a>
                </li>

                {{-- Page Numbers --}}
                @php
                    $start = max(1, $paginator->currentPage() - 2);
                    $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
                @endphp

                @if($start > 1)
                    <li class="kt-pagination-item">
                        <a class="kt-btn kt-btn-icon kt-btn-ghost" href="{{ $paginator->url(1) }}" wire:click.prevent="gotoPage(1)">1</a>
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
                        <a class="kt-btn kt-btn-icon kt-btn-ghost {{ $page == $paginator->currentPage() ? 'active' : '' }}" href="{{ $paginator->url($page) }}" wire:click.prevent="gotoPage({{ $page }})">{{ $page }}</a>
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
                        <a class="kt-btn kt-btn-icon kt-btn-ghost" href="{{ $paginator->url($paginator->lastPage()) }}" wire:click.prevent="gotoPage({{ $paginator->lastPage() }})">{{ $paginator->lastPage() }}</a>
                    </li>
                @endif

                {{-- Next Page --}}
                <li class="kt-pagination-item">
                    <a class="kt-btn kt-btn-icon kt-btn-ghost {{ !$paginator->nextPageUrl() ? 'opacity-50 cursor-not-allowed' : '' }}" 
                       href="{{ $paginator->nextPageUrl() }}" 
                       wire:click.prevent="{{ $paginator->nextPageUrl() ? 'nextPage' : '' }}"
                       @if(!$paginator->nextPageUrl()) style="pointer-events: none;" @endif>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right rtl:rotate-180" aria-hidden="true">
                            <path d="m9 18 6-6-6-6"></path>
                        </svg>
                    </a>
                </li>

                {{-- Last Page --}}
                <li class="kt-pagination-item">
                    <a class="kt-btn kt-btn-icon kt-btn-ghost {{ $paginator->currentPage() >= $paginator->lastPage() ? 'opacity-50 cursor-not-allowed' : '' }}" 
                       href="{{ $paginator->url($paginator->lastPage()) }}" 
                       wire:click.prevent="{{ $paginator->currentPage() < $paginator->lastPage() ? 'gotoPage(' . $paginator->lastPage() . ')' : '' }}"
                       @if($paginator->currentPage() >= $paginator->lastPage()) style="pointer-events: none;" @endif>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-last rtl:rotate-180" aria-hidden="true">
                            <path d="m7 18 6-6-6-6"></path>
                            <path d="M17 6v12"></path>
                        </svg>
                    </a>
                </li>
            </ol>
        </div>
    </div>
</div>
