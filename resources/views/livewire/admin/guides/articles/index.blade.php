<div class="space-y-6">
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            @php
                $activeFilterCount = collect([
                    $filterType,
                    $filterRole,
                ])->filter(fn($value) => $value !== '')->count();
            @endphp
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-5">
                <div>
                    <h1 class="text-2xl font-black">Kelola Artikel Panduan</h1>
                    <p class="text-sm text-base-content/60 mt-1">
                        Buat panduan berbentuk artikel scroll atau video tutorial, lalu tentukan role mana yang bisa melihat.
                    </p>
                </div>
                <a wire:navigate href="{{ route('guides.articles.create') }}" class="btn btn-primary btn-sm gap-2">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Tambah Panduan
                </a>
            </div>

            <div class="flex flex-col md:flex-row gap-3 mb-4">
                <label class="input input-sm input-bordered flex items-center gap-2">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 opacity-50" />
                    <input type="text" class="grow" wire:model.live.debounce.300ms="search" placeholder="Cari judul/isi/video" />
                </label>

                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                        <x-heroicon-o-funnel class="w-5 h-5" />
                        Filter
                        @if ($activeFilterCount > 0)
                            <span class="badge badge-primary badge-sm">{{ $activeFilterCount }}</span>
                        @endif
                    </label>
                    <div tabindex="0" class="dropdown-content z-10 card card-compact w-72 p-4 bg-base-100 border border-base-300 mt-2">
                        <div class="space-y-3">
                            <x-form.select
                                label="Tipe"
                                name="filterType"
                                wire:model.live="filterType"
                                placeholder="Semua Tipe"
                                class="select-sm"
                            >
                                <option value="article">Artikel</option>
                                <option value="video">Video</option>
                            </x-form.select>

                            <x-form.select
                                label="Target Role"
                                name="filterRole"
                                wire:model.live="filterRole"
                                placeholder="Semua Target Role"
                                class="select-sm"
                            >
                                @foreach($roleOptions as $role)
                                    <option value="{{ $role }}">{{ $roleLabels[$role] ?? ucfirst($role) }}</option>
                                @endforeach
                            </x-form.select>

                            <button wire:click="resetFilters" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                        </div>
                    </div>
                </div>
            </div>

            <x-partials.table
                :columns="[
                    ['label' => 'Judul'],
                    ['label' => 'Tipe'],
                    ['label' => 'Target Role'],
                    ['label' => 'Status'],
                    ['label' => 'Urutan'],
                    ['label' => 'Update Terakhir'],
                    ['label' => 'Aksi', 'class' => 'text-center'],
                ]"
                :data="$guides"
                emptyMessage="Belum ada artikel panduan."
                emptyIcon="heroicon-o-book-open"
            >
                @foreach($guides as $guide)
                    <tr wire:key="guide-article-{{ $guide->id }}" class="hover:bg-base-200/50 transition-colors">
                        <td>
                            <div class="font-semibold">{{ $guide->title }}</div>
                            <div class="text-xs text-base-content/60 mt-1 line-clamp-2">
                                @if($guide->guide_type === 'article')
                                    {{ trim(strip_tags((string) $guide->article_body)) }}
                                @else
                                    {{ $guide->video_url }}
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-soft {{ $guide->guide_type === 'video' ? 'badge-info' : 'badge-primary' }}">
                                {{ $guide->guide_type === 'video' ? 'Video' : 'Artikel' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @foreach(($guide->target_roles ?? []) as $role)
                                    <span class="badge badge-ghost badge-xs">{{ $roleLabels[$role] ?? ucfirst($role) }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-soft {{ $guide->is_active ? 'badge-success' : 'badge-error' }}">
                                {{ $guide->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>{{ $guide->sort_order }}</td>
                        <td class="text-xs">{{ $guide->updated_at?->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            <x-partials.dropdown-action
                                :id="$guide->id"
                                :showView="false"
                                :showEdit="false"
                                :showDelete="true"
                                deleteMethod="openDelete"
                                :customActions="[
                                    ['method' => 'openEdit', 'label' => 'Edit', 'icon' => 'heroicon-o-pencil'],
                                ]"
                            />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <x-partials.pagination :paginator="$guides" :perPage="$perPage">
                    <x-slot:center>
                        Menampilkan <span class="font-semibold">{{ $guides->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $guides->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $guides->total() }}</span> data
                    </x-slot:center>
                </x-partials.pagination>
            </div>
        </div>
    </div>

    <x-form.modal
        modalId="edit-guide-article-modal"
        title="Edit Artikel Panduan"
        saveAction="update"
        saveButtonText="Update"
        saveButtonIcon="heroicon-o-check"
        :showButton="false"
        modalSize="modal-box w-11/12 max-w-5xl"
    >
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <x-form.input name="formTitle" wireModel="formTitle" wireModelModifier="live" label="Judul" placeholder="Contoh: Alur Checkout Cashier" />

            <fieldset>
                <legend class="fieldset-legend">Tipe Panduan</legend>
                <select class="select select-bordered w-full" wire:model.live="formType">
                    <option value="article">Artikel (Quill)</option>
                    <option value="video">Video</option>
                </select>
                @error('formType')
                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>

        @if($formType === 'article')
            <div class="mt-4">
                <label class="fieldset-legend">Isi Artikel</label>
                <div id="quill-wrapper-edit" wire:ignore class="border border-base-300 rounded-xl overflow-hidden bg-base-100">
                    <div id="quill-editor-edit" style="height: 280px;"></div>
                </div>
                <input type="hidden" id="guide-article-body-edit-hidden" wire:model.defer="formArticleBody">
                @error('formArticleBody')
                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        @else
            <x-form.input
                name="formVideoUrl"
                wireModel="formVideoUrl"
                wireModelModifier="live"
                label="URL Video"
                placeholder="Contoh: https://www.youtube.com/watch?v=..."
                containerClass="mt-4"
            />
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
            <x-form.input name="formSortOrder" type="number" min="0" wireModel="formSortOrder" wireModelModifier="live" label="Urutan" />

            <fieldset class="lg:col-span-2">
                <legend class="fieldset-legend">Target Role</legend>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 p-3 border border-base-300 rounded-xl">
                    @foreach($roleOptions as $role)
                        <label class="label cursor-pointer justify-start gap-2">
                            <input type="checkbox" class="checkbox checkbox-sm" wire:model.live="formTargetRoles" value="{{ $role }}">
                            <span class="label-text text-sm">{{ $roleLabels[$role] ?? ucfirst($role) }}</span>
                        </label>
                    @endforeach
                </div>
                @error('formTargetRoles')
                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
                @error('formTargetRoles.*')
                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>

        <label class="label cursor-pointer justify-start gap-3 mt-4">
            <input type="checkbox" class="toggle toggle-primary" wire:model.live="formIsActive">
            <span class="label-text">Aktifkan panduan ini</span>
        </label>
    </x-form.modal>

    <x-form.modal
        modalId="delete-guide-article-modal"
        title="Hapus Artikel Panduan"
        saveAction="delete"
        saveButtonText="Hapus"
        saveButtonIcon="heroicon-o-trash"
        saveButtonClass="btn btn-error text-white gap-2 btn-sm"
        :showButton="false"
    >
        <div class="flex flex-col items-center text-center py-2">
            <div class="w-16 h-16 bg-error/10 text-error rounded-full flex items-center justify-center mb-4">
                <x-heroicon-o-trash class="w-10 h-10" />
            </div>
            <h4 class="text-lg font-bold">Apakah Anda yakin?</h4>
            <p class="text-base-content/60 mt-2">
                Artikel <span class="font-semibold text-base-content">{{ $deleteTitle }}</span> akan dihapus permanen.
            </p>
        </div>
    </x-form.modal>

    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <script>
        (function () {
            if (window.__guideArticleEditEditorRegistered) {
                return;
            }
            window.__guideArticleEditEditorRegistered = true;

            function setHiddenValue(hiddenId, value) {
                const hidden = document.getElementById(hiddenId);
                if (!hidden) return;
                hidden.value = value;
                hidden.dispatchEvent(new Event('input', { bubbles: true }));
            }

            function initQuillEditor(config) {
                if (!window.Quill) return;

                const editorElement = document.getElementById(config.editorId);
                if (!editorElement) return;

                if (!window.__guideArticleEditors) {
                    window.__guideArticleEditors = {};
                }

                let quill = window.__guideArticleEditors[config.key] ?? null;
                if (!quill) {
                    quill = new Quill('#' + config.editorId, {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                [{ header: [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ list: 'ordered' }, { list: 'bullet' }],
                                ['link', 'image', 'blockquote', 'code-block'],
                                ['clean'],
                            ],
                        },
                    });

                    quill.on('text-change', function () {
                        setHiddenValue(config.hiddenId, quill.root.innerHTML);
                    });

                    window.__guideArticleEditors[config.key] = quill;

                    if (config.key === 'create') {
                        window.quillCreateInstance = quill;
                    }
                    if (config.key === 'edit') {
                        window.quillEditInstance = quill;
                    }
                }

                const html = String(config.content || '');
                quill.root.innerHTML = html;
                setHiddenValue(config.hiddenId, html);
            }

            document.addEventListener('livewire:init', function () {
                Livewire.on('guides-editor-edit-opened', function (payload) {
                    const data = Array.isArray(payload) ? payload[0] : payload;
                    setTimeout(function () {
                        initQuillEditor({
                            key: 'edit',
                            editorId: 'quill-editor-edit',
                            hiddenId: 'guide-article-body-edit-hidden',
                            content: data?.content || '',
                        });
                    }, 120);
                });
            });
        })();
    </script>
</div>
