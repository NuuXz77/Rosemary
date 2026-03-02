<x-form.modal
    modalId="modal_edit_setting"
    title="Edit Pengaturan"
    saveButtonText="Update"
    saveButtonIcon="heroicon-o-check"
    saveAction="update"
    :showButton="false"
    modalSize="modal-box max-w-2xl">
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- LABEL --}}
        <x-form.input 
            label="Label Pengaturan" 
            name="label" 
            icon="heroicon-o-tag"
            placeholder="Contoh: Nama Aplikasi" 
            wireModel="label" 
            :required="true" />

        {{-- KEY --}}
        <x-form.input 
            label="Key (Unique ID)" 
            name="key" 
            icon="heroicon-o-key"
            placeholder="Contoh: app_name" 
            wireModel="key" 
            :required="true" />

        {{-- GRUP --}}
        <div class="form-control">
            <label class="label">
                <span class="label-text font-semibold">Grup</span>
            </label>
            <select wire:model="group" class="select select-bordered select-sm w-full">
                <option value="general">General</option>
                <option value="contact">Contact</option>
                <option value="social">Social Media</option>
                <option value="system">System</option>
            </select>
        </div>

        {{-- TIPE --}}
        <div class="form-control">
            <label class="label">
                <span class="label-text font-semibold">Tipe Input</span>
            </label>
            <select wire:model="type" class="select select-bordered select-sm w-full">
                <option value="text">Single Line Text</option>
                <option value="textarea">Multi-line Text</option>
                <option value="number">Number</option>
                <option value="boolean">Boolean (True/False)</option>
                <option value="email">Email</option>
            </select>
        </div>

        {{-- VALUE --}}
        <div class="md:col-span-2">
            @if($type === 'boolean')
                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-4">
                        <span class="label-text font-semibold">Nilai Pengaturan</span>
                        <input type="checkbox" wire:model="value" class="toggle toggle-primary" />
                    </label>
                </div>
            @elseif($type === 'textarea')
                <fieldset>
                    <legend class="fieldset-legend">Nilai Pengaturan</legend>
                    <textarea wire:model="value" class="textarea textarea-bordered w-full" rows="3"></textarea>
                </fieldset>
            @else
                <x-form.input 
                    label="Nilai Pengaturan" 
                    name="value" 
                    :type="$type"
                    icon="heroicon-o-pencil-square"
                    wireModel="value" />
            @endif
        </div>

        {{-- DESKRIPSI --}}
        <div class="md:col-span-2">
            <fieldset>
                <legend class="fieldset-legend">Deskripsi (Opsional)</legend>
                <textarea wire:model="description" class="textarea textarea-bordered w-full" rows="2" placeholder="Jelaskan kegunaan pengaturan ini..."></textarea>
            </fieldset>
        </div>
    </div>

</x-form.modal>
