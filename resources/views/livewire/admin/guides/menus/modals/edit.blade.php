<div>
    <x-form.modal
        modalId="edit-guide-menu-modal"
        title="Edit Menu Guide"
        saveButtonText="Perbarui"
        saveAction="update"
        :showButton="false"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.select label="Role" name="role_key" wireModel="role_key" class="select-sm" required>
                <option value="admin">Admin</option>
                <option value="cashier">Cashier</option>
                <option value="production">Production</option>
                <option value="student">Student</option>
            </x-form.select>

            <x-form.input label="Label Menu" name="label" wireModel="label" placeholder="Contoh: Produksi Harian" required size="input-sm" />

            <x-form.select label="Route Name (opsional)" name="route_name" wireModel="route_name" class="select-sm" placeholder="- Tidak Pakai Route -">
                <option value="">- Tidak Pakai Route -</option>
                @foreach($routeOptions as $route)
                    <option value="{{ $route['route'] }}">{{ $route['label'] }} ({{ $route['route'] }})</option>
                @endforeach
            </x-form.select>

            <x-form.input label="External URL (opsional)" name="external_url" type="url" wireModel="external_url" placeholder="https://..." size="input-sm" />

            <x-form.input label="Module Key (opsional)" name="module_key" wireModel="module_key" placeholder="contoh: productions" size="input-sm" />

            <x-form.select label="Permission Wajib (opsional)" name="required_permission" wireModel="required_permission" class="select-sm" placeholder="- Tanpa Permission Khusus -">
                <option value="">- Tanpa Permission Khusus -</option>
                @foreach($permissionOptions as $permission)
                    <option value="{{ $permission }}">{{ $permission }}</option>
                @endforeach
            </x-form.select>

            <x-form.input label="Sort Order" name="sort_order" type="number" wireModel="sort_order" min="0" size="input-sm" />

            <x-form.input label="Deskripsi" name="description" wireModel="description" placeholder="Keterangan singkat menu" size="input-sm" />
        </div>

        <div class="mt-3">
            <x-form.checkbox label="Status Aktif" name="is_active" wireModel="is_active" />
        </div>
    </x-form.modal>
</div>
