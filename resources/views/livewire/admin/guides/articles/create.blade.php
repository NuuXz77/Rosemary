<div class="space-y-6" x-data="{ type: $wire.entangle('formType') }">
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6 space-y-5">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black">Tambah Artikel Panduan</h1>
                    <p class="text-sm text-base-content/60 mt-1">
                        Gunakan halaman penuh agar penulisan konten panduan lebih leluasa.
                    </p>
                </div>

                <a wire:navigate href="{{ route('guides.articles.index') }}" class="btn btn-ghost btn-sm gap-2">
                    <x-heroicon-o-arrow-left class="w-4 h-4" />
                    Kembali ke Daftar
                </a>
            </div>

            <form wire:submit="save" class="space-y-5">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <x-form.input
                        name="formTitle"
                        wireModel="formTitle"
                        wireModelModifier="live"
                        label="Judul"
                        placeholder="Contoh: Alur Checkout Cashier"
                    />

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

                <div x-show="type === 'article'" x-cloak>
                    <label class="fieldset-legend">Isi Artikel</label>
                    <div id="quill-wrapper-create-page" wire:ignore class="border border-base-300 rounded-xl overflow-hidden bg-base-100">
                        <div id="quill-editor-create-page" style="height: 460px;"></div>
                    </div>
                    <input type="hidden" id="guide-article-body-create-page-hidden" wire:model.defer="formArticleBody">
                    @error('formArticleBody')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="type === 'video'" x-cloak>
                    <x-form.input
                        name="formVideoUrl"
                        wireModel="formVideoUrl"
                        wireModelModifier="live"
                        label="URL Video"
                        placeholder="Contoh: https://www.youtube.com/watch?v=..."
                    />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <x-form.input
                        name="formSortOrder"
                        type="number"
                        min="0"
                        wireModel="formSortOrder"
                        wireModelModifier="live"
                        label="Urutan"
                    />

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

                <label class="label cursor-pointer justify-start gap-3">
                    <input type="checkbox" class="toggle toggle-primary" wire:model.live="formIsActive">
                    <span class="label-text">Aktifkan panduan ini</span>
                </label>

                <div class="flex flex-col sm:flex-row gap-2 justify-end pt-3 border-t border-base-300">
                    <a wire:navigate href="{{ route('guides.articles.index') }}" class="btn btn-ghost">Batal</a>
                    <button type="submit" class="btn btn-primary gap-2">
                        <x-heroicon-o-check class="w-4 h-4" />
                        Simpan Panduan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <script>
        (function () {
            if (window.__guideArticleCreatePageEditorRegistered) {
                return;
            }
            window.__guideArticleCreatePageEditorRegistered = true;

            function setHiddenValue(value) {
                const hidden = document.getElementById('guide-article-body-create-page-hidden');
                if (!hidden) return;
                hidden.value = value;
                hidden.dispatchEvent(new Event('input', { bubbles: true }));
            }

            function initCreateEditor() {
                if (!window.Quill) return;
                const editorElement = document.getElementById('quill-editor-create-page');
                if (!editorElement) return;

                if (window.__guideArticleCreatePageEditor && !document.body.contains(window.__guideArticleCreatePageEditor.root)) {
                    window.__guideArticleCreatePageEditor = null;
                }

                if (!window.__guideArticleCreatePageEditor) {
                    const quill = new Quill('#quill-editor-create-page', {
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
                        setHiddenValue(quill.root.innerHTML);
                    });

                    window.__guideArticleCreatePageEditor = quill;
                }

                const hidden = document.getElementById('guide-article-body-create-page-hidden');
                const html = hidden ? String(hidden.value || '') : '';
                window.__guideArticleCreatePageEditor.root.innerHTML = html;
                setHiddenValue(html);
            }

            document.addEventListener('livewire:init', function () {
                setTimeout(initCreateEditor, 120);
            });

            document.addEventListener('livewire:navigated', function () {
                setTimeout(initCreateEditor, 120);
            });
        })();
    </script>
</div>
