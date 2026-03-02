@props(['id', 'title'])

<dialog id="{{ $id }}" class="modal" x-data
    x-on:open-modal.window="if ($event.detail.id === '{{ $id }}') { $el.showModal(); }"
    x-on:close-modal.window="if ($event.detail.id === '{{ $id }}') { $el.close(); }" wire:ignore.self>
    <div class="modal-box w-11/12 max-w-5xl">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>

        @if($title)
            <h3 class="font-bold text-lg">{{ $title }}</h3>
        @endif

        <div class="py-4">
            {{ $slot }}
        </div>
    </div>

    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>