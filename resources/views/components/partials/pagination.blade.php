@props(['paginator', 'perPage' => 10])

<div class="flex flex-col sm:flex-row justify-between items-center gap-4">
    <!-- Left: Per Page Selector -->
    <div class="flex items-center gap-2">
        <span class="text-sm text-gray-600">Tampilkan</span>
        <select wire:model.live="perPage" class="select select-bordered select-sm w-20">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="15">15</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="{{ $paginator->total() }}">All</option>
        </select>
        <span class="text-sm text-gray-600">data</span>
    </div>

    <!-- Center: Page Info (Mobile) -->
    <div class="text-sm text-gray-600 sm:hidden">
        Halaman <span class="font-semibold">{{ $paginator->currentPage() }}</span> 
        dari <span class="font-semibold">{{ $paginator->lastPage() }}</span>
    </div>

    <!-- Right: Pagination Controls -->
    <div>
        @if ($paginator->hasPages())
            <!-- Desktop Pagination -->
            <div class="hidden sm:block">
                <div class="join">
                    {{-- Previous Button --}}
                    @if ($paginator->onFirstPage())
                        <button class="join-item btn btn-sm btn-disabled">
                            <x-heroicon-o-chevron-left class="w-4 h-4" />
                        </button>
                    @else
                        <button wire:click="previousPage" wire:loading.attr="disabled" class="join-item btn btn-sm">
                            <x-heroicon-o-chevron-left class="w-4 h-4" />
                        </button>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <button class="join-item btn btn-sm btn-active">{{ $page }}</button>
                        @elseif ($page == 1 || $page == $paginator->lastPage() || ($page >= $paginator->currentPage() - 1 && $page <= $paginator->currentPage() + 1))
                            <button wire:click="gotoPage({{ $page }})" class="join-item btn btn-sm">{{ $page }}</button>
                        @elseif ($page == $paginator->currentPage() - 2 || $page == $paginator->currentPage() + 2)
                            <button class="join-item btn btn-sm btn-disabled">...</button>
                        @endif
                    @endforeach

                    {{-- Next Button --}}
                    @if ($paginator->hasMorePages())
                        <button wire:click="nextPage" wire:loading.attr="disabled" class="join-item btn btn-sm">
                            <x-heroicon-o-chevron-right class="w-4 h-4" />
                        </button>
                    @else
                        <button class="join-item btn btn-sm btn-disabled">
                            <x-heroicon-o-chevron-right class="w-4 h-4" />
                        </button>
                    @endif
                </div>
            </div>

            <!-- Mobile Pagination -->
            <div class="sm:hidden">
                <div class="join">
                    @if ($paginator->onFirstPage())
                        <button class="join-item btn btn-sm btn-disabled">«</button>
                    @else
                        <button wire:click="previousPage" class="join-item btn btn-sm">«</button>
                    @endif
                    
                    <button class="join-item btn btn-sm btn-active">
                        {{ $paginator->currentPage() }}/{{ $paginator->lastPage() }}
                    </button>
                    
                    @if ($paginator->hasMorePages())
                        <button wire:click="nextPage" class="join-item btn btn-sm">»</button>
                    @else
                        <button class="join-item btn btn-sm btn-disabled">»</button>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
