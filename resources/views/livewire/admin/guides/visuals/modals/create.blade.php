<div>
    <x-form.modal
        modalId="create-guide-visual-modal"
        title="Tambah Visual Guide"
        buttonText="Tambah Visual"
        buttonIcon="heroicon-o-plus"
        saveAction="save"
        :showButton="true"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.select label="Role" name="role_key" wireModel="role_key" class="select-sm" required>
                <option value="admin">Admin</option>
                <option value="cashier">Cashier</option>
                <option value="production">Production</option>
                <option value="student">Student</option>
            </x-form.select>

            <x-form.input label="Sort Order" name="sort_order" type="number" wireModel="sort_order" min="0" size="input-sm" />
            <x-form.input label="Judul" name="title" wireModel="title" placeholder="Contoh: Proses Checkout" size="input-sm" required />
            <x-form.input label="Module Key (opsional)" name="module_key" wireModel="module_key" placeholder="contoh: productions" size="input-sm" />

            <x-form.select label="Permission Wajib (opsional)" name="required_permission" wireModel="required_permission" class="select-sm" placeholder="- Tanpa Permission Khusus -">
                <option value="">- Tanpa Permission Khusus -</option>
                @foreach($permissionOptions as $permission)
                    <option value="{{ $permission }}">{{ $permission }}</option>
                @endforeach
            </x-form.select>

            <x-form.input label="Deskripsi" name="body" wireModel="body" placeholder="Deskripsi visual" size="input-sm" required />
            <x-form.input label="Media URL" name="media_url" type="url" wireModel="media_url" placeholder="https://..." size="input-sm" />

            <x-form.input
                label="Upload Gambar/GIF"
                name="mediaFile"
                type="file"
                wireModel="mediaFile"
                accept="image/*"
                size="input-sm"
                containerClass="md:col-span-2"
                hint="File disimpan ke storage/app/public/guides/visuals (maks 5MB)."
            />
        </div>

        @if($mediaFile)
            <div class="mt-3 rounded-xl border border-base-300 p-3 bg-base-200/40 max-w-sm">
                <p class="text-xs font-semibold mb-2">Preview Upload</p>
                <img src="{{ $mediaFile->temporaryUrl() }}" alt="Preview media" class="rounded-lg w-full h-40 object-cover" />
            </div>
        @elseif($media_url)
            <div class="mt-3 rounded-xl border border-base-300 p-3 bg-base-200/40 max-w-sm">
                <p class="text-xs font-semibold mb-2">Media URL</p>
                <img src="{{ $media_url }}" alt="Preview media url" class="rounded-lg w-full h-40 object-cover" />
            </div>
        @endif

        <div class="alert alert-info mt-3 text-xs">
            <x-heroicon-o-information-circle class="w-4 h-4" />
            <span>Isi URL manual atau upload file. Jika upload dipilih, URL media akan terisi otomatis.</span>
        </div>

        <div class="mt-3">
            <x-form.checkbox label="Status Aktif" name="is_active" wireModel="is_active" />
        </div>
    </x-form.modal>
</div>
