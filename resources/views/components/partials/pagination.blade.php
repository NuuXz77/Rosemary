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

    <!-- Center: Optional Slot -->
    @isset($center)
        <div class="text-sm text-gray-600 text-center">{{ $center }}</div>
    @endisset

    <!-- Right: Pagination Controls -->
    <div>
        @if ($paginator->hasPages())
            <!-- Desktop Pagination -->
            <div class="hidden sm:block">
                <div class="flex items-center gap-1">
                    {{-- Previous Button --}}
                    @if ($paginator->onFirstPage())
                        <button class="btn btn-sm btn-ghost rounded-full opacity-40" disabled>
                            <x-heroicon-o-chevron-left class="w-4 h-4" />
                        </button>
                    @else
                        <button wire:click="previousPage" wire:loading.attr="disabled" class="btn btn-sm btn-ghost rounded-full">
                            <x-heroicon-o-chevron-left class="w-4 h-4" />
                        </button>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <button class="btn btn-sm btn-primary rounded-full min-w-[2rem]">{{ $page }}</button>
                        @elseif ($page == 1 || $page == $paginator->lastPage() || ($page >= $paginator->currentPage() - 1 && $page <= $paginator->currentPage() + 1))
                            <button wire:click="gotoPage({{ $page }})" class="btn btn-sm btn-ghost rounded-full min-w-[2rem]">{{ $page }}</button>
                        @elseif ($page == $paginator->currentPage() - 2 || $page == $paginator->currentPage() + 2)
                            <button class="btn btn-sm btn-ghost rounded-full min-w-[2rem]" disabled>...</button>
                        @endif
                    @endforeach

                    {{-- Next Button --}}
                    @if ($paginator->hasMorePages())
                        <button wire:click="nextPage" wire:loading.attr="disabled" class="btn btn-sm btn-ghost rounded-full">
                            <x-heroicon-o-chevron-right class="w-4 h-4" />
                        </button>
                    @else
                        <button class="btn btn-sm btn-ghost rounded-full opacity-40" disabled>
                            <x-heroicon-o-chevron-right class="w-4 h-4" />
                        </button>
                    @endif
                </div>
            </div>

            <!-- Mobile Pagination -->
            <div class="sm:hidden">
                <div class="flex items-center gap-1">
                    @if ($paginator->onFirstPage())
                        <button class="btn btn-sm btn-ghost rounded-full opacity-40" disabled>«</button>
                    @else
                        <button wire:click="previousPage" class="btn btn-sm btn-ghost rounded-full">«</button>
                    @endif
                    
                    <button class="btn btn-sm btn-primary rounded-full px-3">
                        {{ $paginator->currentPage() }}/{{ $paginator->lastPage() }}
                    </button>
                    
                    @if ($paginator->hasMorePages())
                        <button wire:click="nextPage" class="btn btn-sm btn-ghost rounded-full">»</button>
                    @else
                        <button class="btn btn-sm btn-ghost rounded-full opacity-40" disabled>»</button>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
