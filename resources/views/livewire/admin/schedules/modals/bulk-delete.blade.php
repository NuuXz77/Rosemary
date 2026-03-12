<div>
    <x-form.modal
        modalId="bulk-delete-schedule-modal"
        title="Hapus Jadwal Massal"
        saveAction="bulkDelete"
        saveButtonText="Hapus Jadwal"
        saveButtonIcon="heroicon-o-trash"
        saveButtonClass="btn btn-error text-white gap-2 btn-sm"
        :showButton="false">

        <div class="space-y-4">
            {{-- Warning Section --}}
            <div class="alert alert-warning">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                <div>
                    <h4 class="font-bold">Perhatian!</h4>
                    <p class="text-sm">Pilih filter untuk menentukan jadwal yang akan dihapus. Proses ini tidak dapat dibatalkan.</p>
                </div>
            </div>

            {{-- Filter Tipe --}}
            <x-form.select
                label="Tipe Jadwal"
                name="type"
                wire:model.live="type"
                placeholder="-- Pilih Tipe --"
                icon="heroicon-o-tag"
                :required="true"
                validatorMessage="Pilih tipe jadwal">
                <option value="cashier">Kasir</option>
                <option value="production">Produksi</option>
            </x-form.select>

            {{-- Filter Tanggal --}}
            <div class="grid grid-cols-2 gap-3">
                <x-form.input
                    label="Dari Tanggal"
                    name="startDate"
                    type="date"
                    wireModel="startDate"
                    wireModelModifier="live"
                    icon="heroicon-o-calendar"
                    :required="true"
                    validatorMessage="Tanggal awal wajib diisi" />

                <x-form.input
                    label="Sampai Tanggal"
                    name="endDate"
                    type="date"
                    wireModel="endDate"
                    wireModelModifier="live"
                    icon="heroicon-o-calendar"
                    :required="true"
                    validatorMessage="Tanggal akhir wajib diisi" />
            </div>

            {{-- Filter Divisi (dinamis) --}}
            <x-form.select
                label="Divisi (Opsional)"
                name="divisionId"
                wire:model.live="divisionId"
                placeholder="Semua Divisi"
                icon="heroicon-o-building-office-2">
                @if ($type === 'cashier')
                    @foreach ($divisionsCashier as $division)
                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                    @endforeach
                @elseif ($type === 'production')
                    @foreach ($divisionsProduction as $division)
                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                    @endforeach
                @endif
            </x-form.select>

            {{-- Filter Shift --}}
            <x-form.select
                label="Shift (Opsional)"
                name="shiftId"
                wire:model.live="shiftId"
                placeholder="Semua Shift"
                icon="heroicon-o-clock">
                @foreach ($shifts as $shift)
                    <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                @endforeach
            </x-form.select>

            {{-- Preview Count --}}
            @if($previewCount !== null)
                <div class="alert {{ $previewCount > 0 ? 'alert-info' : 'alert-warning' }}">
                    <x-heroicon-o-information-circle class="w-5 h-5" />
                    <div>
                        <h4 class="font-bold">Pratinjau</h4>
                        <p class="text-sm">
                            @if($previewCount > 0)
                                Akan menghapus <span class="font-bold text-lg">{{ $previewCount }}</span> jadwal
                            @else
                                Tidak ada jadwal yang sesuai dengan filter
                            @endif
                        </p>
                    </div>
                </div>
            @endif
        </div>

    </x-form.modal>
</div>
