<div class="space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">
                🚗 Master Mobil Listrik (EV)
            </flux:heading>
            <flux:subheading class="text-xs text-zinc-500 mt-1">
                Katalog data mobil listrik di Indonesia: kapasitas baterai, estimasi jarak tempuh, serta tipe colokan AC & DC.
            </flux:subheading>
        </div>

        <flux:button wire:click="create" variant="primary" icon="plus">
            Tambah Mobil Listrik
        </flux:button>
    </div>

    {{-- Filter Card --}}
    <flux:card class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Cari merk, model, varian..."
                icon="magnifying-glass"
                clearable
            />

            <flux:select wire:model.live="brandFilter">
                <flux:select.option value="">Semua Pabrikan / Merk</flux:select.option>
                @foreach ($availableBrands as $b)
                    <flux:select.option value="{{ $b }}">{{ $b }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="dcFilter">
                <flux:select.option value="">Semua Pengisian DC</flux:select.option>
                <flux:select.option value="yes">Mendukung DC Fast Charging</flux:select.option>
                <flux:select.option value="no">Hanya AC (Tanpa Port DC)</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="statusFilter">
                <flux:select.option value="">Semua Status</flux:select.option>
                <flux:select.option value="active">Hanya Aktif</flux:select.option>
                <flux:select.option value="inactive">Nonaktif</flux:select.option>
            </flux:select>
        </div>

        {{-- PowerGrid Table --}}
        <div>
            <livewire:tables.ev-model-table
                :search="$search"
                :brand-filter="$brandFilter"
                :dc-filter="$dcFilter"
                :status-filter="$statusFilter"
                :key="'ev-model-table-'.$search.'-'.$brandFilter.'-'.$dcFilter.'-'.$statusFilter"
            />
        </div>
    </flux:card>

    {{-- Create / Edit Modal (Wide Format) --}}
    <flux:modal name="ev-model-modal" class="w-full max-w-3xl lg:max-w-4xl space-y-6">
        <div>
            <flux:heading size="lg" class="font-bold">
                {{ $form->evModel ? 'Edit Data Mobil Listrik' : 'Tambah Mobil Listrik Baru' }}
            </flux:heading>
            <flux:subheading class="text-xs text-zinc-500">
                Isi spesifikasi pabrikan, kapasitas baterai, estimasi jarak tempuh, dan jenis port pengisian
            </flux:subheading>
        </div>

        <form wire:submit="save" class="space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {{-- Brand --}}
                <div>
                    <flux:field>
                        <flux:label>Merk / Pabrikan *</flux:label>
                        <flux:input wire:model="form.brand" placeholder="cth: Wuling / Hyundai / BYD" required />
                        <flux:error name="form.brand" />
                    </flux:field>
                </div>

                {{-- Model Name --}}
                <div>
                    <flux:field>
                        <flux:label>Nama Model *</flux:label>
                        <flux:input wire:model="form.model_name" placeholder="cth: BinguoEV / Ioniq 5" required />
                        <flux:error name="form.model_name" />
                    </flux:field>
                </div>

                {{-- Variant --}}
                <div>
                    <flux:field>
                        <flux:label>Varian / Tipe</flux:label>
                        <flux:input wire:model="form.variant" placeholder="cth: 410 km / Long Range" />
                        <flux:error name="form.variant" />
                    </flux:field>
                </div>

                {{-- Battery Capacity --}}
                <div>
                    <flux:field>
                        <flux:label>Kapasitas Baterai (kWh) *</flux:label>
                        <flux:input wire:model="form.battery_capacity_kwh" type="number" step="0.1" min="1" max="300" placeholder="cth: 37.9" required />
                        <flux:error name="form.battery_capacity_kwh" />
                    </flux:field>
                </div>

                {{-- Range --}}
                <div>
                    <flux:field>
                        <flux:label>Estimasi Jarak Tempuh (km) *</flux:label>
                        <flux:input wire:model="form.range_km" type="number" min="10" max="2000" placeholder="cth: 410" required />
                        <flux:error name="form.range_km" />
                    </flux:field>
                </div>

                {{-- Is Active --}}
                <div class="flex items-center pt-6">
                    <flux:checkbox wire:model="form.is_active" label="Aktifkan di Pilihan Mobile Apps" />
                </div>
            </div>

            <flux:separator />

            {{-- Charging Port Specs --}}
            <div>
                <flux:heading size="sm" class="font-bold text-zinc-900 dark:text-zinc-100 mb-3">
                    Spesifikasi Port Pengisian (AC & DC)
                </flux:heading>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- AC Port --}}
                    <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 space-y-3">
                        <div class="font-semibold text-xs text-zinc-700 dark:text-zinc-300 flex items-center gap-1.5">
                            <span>🔌 Pengisian Arus AC (Normal)</span>
                        </div>
                        <flux:field>
                            <flux:label class="text-xs">Tipe Colokan AC *</flux:label>
                            <flux:select wire:model="form.ac_plug_type" size="sm" required>
                                <flux:select.option value="GBT">GB/T AC (Wuling / Seres)</flux:select.option>
                                <flux:select.option value="TYPE2">Type 2 / Mennekes (Standar Umum)</flux:select.option>
                                <flux:select.option value="OTHER">Lainnya</flux:select.option>
                            </flux:select>
                            <flux:error name="form.ac_plug_type" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="text-xs">Daya Maksimal AC (kW)</flux:label>
                            <flux:input wire:model="form.max_ac_kw" type="number" step="0.1" min="0.5" max="50" placeholder="cth: 6.6" size="sm" />
                            <flux:error name="form.max_ac_kw" />
                        </flux:field>
                    </div>

                    {{-- DC Port --}}
                    <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 space-y-3">
                        <div class="font-semibold text-xs text-zinc-700 dark:text-zinc-300 flex items-center gap-1.5">
                            <span>⚡ Pengisian Arus DC (Fast Charging)</span>
                        </div>
                        <flux:field>
                            <flux:label class="text-xs">Tipe Colokan DC</flux:label>
                            <flux:select wire:model="form.dc_plug_type" size="sm">
                                <flux:select.option value="">Tidak Mendukung DC Fast Charging</flux:select.option>
                                <flux:select.option value="GBT">GB/T DC (Wuling Binguo / Cloud)</flux:select.option>
                                <flux:select.option value="CCS2">CCS Combo 2 (Standar Internasional)</flux:select.option>
                                <flux:select.option value="CHADEMO">CHAdeMO (Nissan Leaf)</flux:select.option>
                                <flux:select.option value="OTHER">Lainnya</flux:select.option>
                            </flux:select>
                            <flux:error name="form.dc_plug_type" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="text-xs">Daya Maksimal DC (kW)</flux:label>
                            <flux:input wire:model="form.max_dc_kw" type="number" step="1" min="5" max="500" placeholder="cth: 50" size="sm" />
                            <flux:error name="form.max_dc_kw" />
                        </flux:field>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <flux:field>
                <flux:label>Catatan Tambahan</flux:label>
                <flux:textarea wire:model="form.notes" rows="2" placeholder="Informasi adaptor, dukungan V2L, arsitektur tegangan, dll." />
                <flux:error name="form.notes" />
            </flux:field>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    Simpan Mobil Listrik
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>

