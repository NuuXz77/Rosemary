<div class="space-y-6">
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6 space-y-5">
            @php
                $activeFilterCount = $filterType !== 'all' ? 1 : 0;
            @endphp
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black">Pusat Panduan</h1>
                    <p class="text-sm text-base-content/60 mt-1">
                        Panduan baru berbentuk artikel scroll dan video tutorial. Konten tampil sesuai role yang dipilih.
                    </p>
                </div>

                @if($canManageGuides)
                    <a wire:navigate href="{{ route('guides.articles.index') }}" class="btn btn-primary btn-sm gap-2">
                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                        Kelola Artikel Panduan
                    </a>
                @endif
            </div>

            <div role="tablist" class="tabs tabs-boxed w-full overflow-x-auto flex-nowrap gap-2">
                @foreach($availableRoles as $role)
                    <button
                        class="tab {{ $activeRole === $role ? 'tab-active' : '' }}"
                        wire:click="setRole('{{ $role }}')"
                        type="button"
                    >
                        {{ $roleLabels[$role] ?? ucfirst($role) }}
                    </button>
                @endforeach
            </div>

            <div class="flex flex-col md:flex-row gap-3">
                <label class="input input-sm input-bordered flex items-center gap-2 w-full md:flex-1">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 opacity-50" />
                    <input type="text" class="grow" wire:model.live.debounce.300ms="search" placeholder="Cari judul atau isi panduan" />
                </label>

                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                        <x-heroicon-o-funnel class="w-5 h-5" />
                        Filter
                        @if ($activeFilterCount > 0)
                            <span class="badge badge-primary badge-sm">{{ $activeFilterCount }}</span>
                        @endif
                    </label>
                    <div tabindex="0" class="dropdown-content z-10 card card-compact w-64 p-4 bg-base-100 border border-base-300 mt-2">
                        <div class="space-y-2">
                            <button type="button" class="btn btn-sm w-full {{ $filterType === 'all' ? 'btn-primary' : 'btn-ghost' }}" wire:click="setFilterType('all')">
                                Semua
                            </button>
                            <button type="button" class="btn btn-sm w-full {{ $filterType === 'article' ? 'btn-primary' : 'btn-ghost' }}" wire:click="setFilterType('article')">
                                Artikel
                            </button>
                            <button type="button" class="btn btn-sm w-full {{ $filterType === 'video' ? 'btn-primary' : 'btn-ghost' }}" wire:click="setFilterType('video')">
                                Video
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4 max-h-[72vh] overflow-y-auto pr-1">
                @forelse($guides as $guide)
                    <article class="rounded-2xl border border-base-300 bg-base-200/30 overflow-hidden">
                        <div class="px-5 py-4 border-b border-base-300 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="font-bold text-lg">{{ $guide['title'] }}</h2>
                                <p class="text-xs text-base-content/60 mt-1">
                                    Diperbarui {{ $guide['updated_at']?->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <span class="badge badge-soft {{ $guide['guide_type'] === 'video' ? 'badge-info' : 'badge-primary' }}">
                                {{ $guide['guide_type'] === 'video' ? 'Video Tutorial' : 'Artikel Tutorial' }}
                            </span>
                        </div>

                        <div class="p-5">
                            @if($guide['guide_type'] === 'article')
                                <div class="prose prose-sm max-w-none prose-headings:my-2 prose-p:my-2 prose-ul:my-2 prose-ol:my-2">
                                    {!! $guide['article_body'] !!}
                                </div>
                            @else
                                @if(!empty($guide['video_embed_url']))
                                    <div class="aspect-video rounded-xl overflow-hidden border border-base-300">
                                        <iframe
                                            src="{{ $guide['video_embed_url'] }}"
                                            title="{{ $guide['title'] }}"
                                            class="w-full h-full"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            referrerpolicy="strict-origin-when-cross-origin"
                                            allowfullscreen
                                        ></iframe>
                                    </div>
                                @elseif(!empty($guide['video_url']))
                                    <a href="{{ $guide['video_url'] }}" target="_blank" class="link link-primary text-sm">
                                        Buka Video Tutorial
                                    </a>
                                @endif
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-base-300 p-10 text-center">
                        <x-heroicon-o-book-open class="w-10 h-10 mx-auto text-base-content/40" />
                        <p class="font-semibold mt-3">Belum ada panduan untuk role ini.</p>
                        <p class="text-sm text-base-content/60 mt-1">
                            @if($canManageGuides)
                                Buat konten baru dari menu Kelola Artikel Panduan.
                            @else
                                Hubungi admin untuk menambahkan panduan.
                            @endif
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
