@props([
    'id',
    'showView' => false,
    'showEdit' => true,
    'showDelete' => true,
    'viewMethod' => 'view',
    'editMethod' => 'edit',
    'deleteMethod' => 'confirmDelete',
    'editModalId' => 'modal_edit',
    'deleteModalId' => 'modal_delete',
    'detailModalId' => 'modal_detail',
    'customActions' => [],
    'viewRoute' => null,
    'editRoute' => null,
    'triggerButtonClass' => 'btn btn-ghost btn-sm btn-square',
    'triggerIconClass' => 'w-5 h-5',
])

<div
    class="relative"
    x-data="{ open: false, coords: { top: 0, left: 0 } }"
    @keydown.escape.window="open = false"
>
    <button
        type="button"
        class="{{ $triggerButtonClass }}"
        x-ref="trigger"
        @click="
            open = !open;
            if (open) {
                $nextTick(() => {
                    const trigger = $refs.trigger.getBoundingClientRect();
                    const menu = $refs.menu.getBoundingClientRect();
                    const top = Math.max(8, trigger.top - menu.height - 8);
                    const left = Math.max(8, trigger.right - menu.width);
                    coords = { top, left };
                });
            }
        "
    >
        <x-heroicon-o-ellipsis-vertical class="{{ $triggerIconClass }}" />
    </button>

    @teleport('body')
        <ul
            x-ref="menu"
            x-show="open"
            x-transition
            @click.outside="open = false"
            class="menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300 fixed z-9999"
            :style="`top: ${coords.top}px; left: ${coords.left}px;`"
        >
        @if($showView)
            <li>
                @if($viewRoute)
                    <a wire:navigate href="{{ $viewRoute }}" class="flex items-center gap-2">
                        <x-heroicon-o-eye class="w-4 h-4" />
                        <span>Lihat Detail</span>
                    </a>
                @else
                    <button wire:click="{{ $viewMethod }}({{ $id }})" class="flex items-center gap-2">
                        <x-heroicon-o-eye class="w-4 h-4" />
                        <span>Lihat Detail</span>
                    </button>
                @endif
            </li>
        @endif

        @if($showEdit)
            <li>
                @if($editRoute)
                    <a wire:navigate href="{{ $editRoute }}" class="flex items-center gap-2">
                        <x-heroicon-o-pencil class="w-4 h-4" />
                        <span>Edit</span>
                    </a>
                @else
                    <button wire:click="{{ $editMethod }}({{ $id }})" class="flex items-center gap-2">
                        <x-heroicon-o-pencil class="w-4 h-4" />
                        <span>Edit</span>
                    </button>
                @endif
            </li>
        @endif

        @foreach($customActions as $action)
            <li>
                <button wire:click="{{ $action['method'] }}({{ $id }})" class="flex items-center gap-2 {{ $action['class'] ?? '' }}">
                    @if(isset($action['icon']))
                        @php
                            $iconName = str_replace('heroicon-', '', $action['icon']);
                            $iconComponent = 'heroicon-' . $iconName;
                        @endphp
                        <x-dynamic-component :component="$iconComponent" class="w-4 h-4" />
                    @endif
                    <span>{{ $action['label'] }}</span>
                </button>
            </li>
        @endforeach

        @if($showDelete)
            <li>
                <button wire:click="{{ $deleteMethod }}({{ $id }})" class="flex items-center gap-2 text-error hover:bg-error hover:text-error-content">
                    <x-heroicon-o-trash class="w-4 h-4" />
                    <span>Hapus</span>
                </button>
            </li>
        @endif
        </ul>
    @endteleport
</div>