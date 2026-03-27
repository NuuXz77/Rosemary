<div>
    <x-form.modal
        modalId="create-guide-step-modal"
        title="Tambah Step Guide"
        buttonText="Tambah Step"
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
            <x-form.input label="Judul (opsional)" name="title" wireModel="title" placeholder="Contoh: Step 1" size="input-sm" />
            <x-form.input label="Module Key (opsional)" name="module_key" wireModel="module_key" placeholder="contoh: productions" size="input-sm" />

            <x-form.select label="Permission Wajib (opsional)" name="required_permission" wireModel="required_permission" class="select-sm" placeholder="- Tanpa Permission Khusus -">
                <option value="">- Tanpa Permission Khusus -</option>
                @foreach($permissionOptions as $permission)
                    <option value="{{ $permission }}">{{ $permission }}</option>
                @endforeach
            </x-form.select>

            <x-form.input label="Isi Step" name="body" wireModel="body" placeholder="Isi langkah kerja" size="input-sm" containerClass="md:col-span-2" />
        </div>

        <div class="mt-3">
            <x-form.checkbox label="Status Aktif" name="is_active" wireModel="is_active" />
        </div>
    </x-form.modal>
</div>
