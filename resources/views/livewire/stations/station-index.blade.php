<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" class="font-bold">Stasiun SPKLU</flux:heading>
            <flux:subheading class="text-zinc-500">
                Kelola data charging station, titik lokasi, dan tipe colokan beserta status kompatibilitas GB/T
            </flux:subheading>
        </div>
        <div>
            <flux:button wire:click="create" variant="primary" icon="plus" class="bg-emerald-600 hover:bg-emerald-500 text-white font-medium">
                Tambah Stasiun
            </flux:button>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <flux:card class="p-4 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari stasiun, operator..."
                    icon="magnifying-glass"
                    clearable
                />
            </div>

            <div>
                <flux:select wire:model.live="current_type" placeholder="Arus (AC/DC)">
                    <flux:select.option value="all">Semua Arus</flux:select.option>
                    <flux:select.option value="DC">DC Fast Charging</flux:select.option>
                    <flux:select.option value="AC">AC Normal Charging</flux:select.option>
                </flux:select>
            </div>

            <div>
                <flux:select wire:model.live="plug_type" placeholder="Tipe Colokan">
                    <flux:select.option value="all">Semua Colokan</flux:select.option>
                    <flux:select.option value="GBT">GB/T (Native)</flux:select.option>
                    <flux:select.option value="TYPE2">Type 2 (Mennekes)</flux:select.option>
                    <flux:select.option value="CCS2">CCS Combo 2</flux:select.option>
                    <flux:select.option value="CHADEMO">CHAdeMO</flux:select.option>
                    <flux:select.option value="OTHER">Lainnya</flux:select.option>
                </flux:select>
            </div>

            <div>
                <flux:select wire:model.live="compat_status" placeholder="Kompatibilitas">
                    <flux:select.option value="all">Semua Status</flux:select.option>
                    <flux:select.option value="native">Native (Direct)</flux:select.option>
                    <flux:select.option value="adapter_ok">Adaptor/Kabel OK</flux:select.option>
                    <flux:select.option value="untested">Belum Dites (Untested)</flux:select.option>
                    <flux:select.option value="adapter_failed">Adaptor Gagal</flux:select.option>
                    <flux:select.option value="incompatible">Tidak Kompatibel</flux:select.option>
                </flux:select>
            </div>
        </div>
    </flux:card>

    {{-- Stations Table --}}
    <flux:card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-10">Fav</flux:table.column>
                    <flux:table.column>Nama & Operator</flux:table.column>
                    <flux:table.column>Lokasi & Koordinat</flux:table.column>
                    <flux:table.column>Konektor & Kompatibilitas GB/T</flux:table.column>
                    <flux:table.column>Operasional & Tarif</flux:table.column>
                    <flux:table.column class="text-right">Aksi</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($stations as $station)
                        <flux:table.row :key="$station->id">
                            {{-- Favorite toggle --}}
                            <flux:table.cell>
                                <button
                                    type="button"
                                    wire:click="toggleFavorite({{ $station->id }})"
                                    class="text-lg hover:scale-110 transition-transform cursor-pointer"
                                    title="{{ $station->is_favorite ? 'Hapus Favorit' : 'Jadikan Favorit' }}"
                                >
                                    @if ($station->is_favorite)
                                        ⭐
                                    @else
                                        <span class="text-zinc-300 dark:text-zinc-600">☆</span>
                                    @endif
                                </button>
                            </flux:table.cell>

                            {{-- Name & Operator --}}
                            <flux:table.cell>
                                <div class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $station->name }}
                                </div>
                                <div class="text-xs text-zinc-500 font-medium">
                                    {{ $station->operator ?? 'Operator Tidak Diketahui' }}
                                </div>
                            </flux:table.cell>

                            {{-- Location --}}
                            <flux:table.cell class="max-w-xs">
                                <div class="text-xs text-zinc-600 dark:text-zinc-300 truncate">
                                    {{ $station->address ?? '-' }}
                                </div>
                                <div class="text-[11px] font-mono text-zinc-400 mt-0.5">
                                    {{ number_format((float) $station->latitude, 5) }}, {{ number_format((float) $station->longitude, 5) }}
                                </div>
                            </flux:table.cell>

                            {{-- Connectors --}}
                            <flux:table.cell>
                                <div class="flex flex-wrap gap-1.5 max-w-sm">
                                    @forelse ($station->connectors as $connector)
                                        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs border
                                            @if ($connector->compat_status->value === 'native')
                                                bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800
                                            @elseif ($connector->compat_status->value === 'adapter_ok')
                                                bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border-sky-300 dark:border-sky-800
                                            @elseif ($connector->compat_status->value === 'untested')
                                                bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-300 dark:border-amber-800
                                            @else
                                                bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-300 dark:border-rose-800
                                            @endif
                                        ">
                                            <span class="font-bold">{{ $connector->current_type->value }}</span>
                                            <span>{{ $connector->plug_type->value }} ({{ (int)$connector->power_kw }}kW)</span>
                                            <span class="text-[10px] opacity-80">• {{ $connector->compat_status->label() }}</span>
                                        </div>
                                    @empty
                                        <span class="text-xs text-zinc-400 italic">Belum ada konektor</span>
                                    @endforelse
                                </div>
                            </flux:table.cell>

                            {{-- Hours & Pricing --}}
                            <flux:table.cell class="text-xs">
                                <div class="text-zinc-700 dark:text-zinc-300">
                                    {{ $station->opening_hours ?? '24 Jam' }}
                                </div>
                                <div class="text-zinc-500 truncate max-w-[180px]">
                                    {{ $station->pricing_note ?? '-' }}
                                </div>
                            </flux:table.cell>

                            {{-- Actions --}}
                            <flux:table.cell class="text-right">
                                <div class="inline-flex items-center gap-1">
                                    <flux:button
                                        wire:click="edit({{ $station->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="pencil-square"
                                        title="Edit"
                                    />
                                    <flux:button
                                        wire:click="delete({{ $station->id }})"
                                        wire:confirm="Yakin ingin menghapus stasiun ini?"
                                        variant="ghost"
                                        size="sm"
                                        icon="trash"
                                        class="text-rose-600 hover:text-rose-700 dark:text-rose-400"
                                        title="Hapus"
                                    />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center py-10 text-zinc-400">
                                Tidak ada data stasiun SPKLU yang cocok dengan pencarian / filter.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        @if ($stations->hasPages())
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
                {{ $stations->links() }}
            </div>
        @endif
    </flux:card>

    {{-- Create / Edit Modal with Repeater Connectors --}}
    <flux:modal name="station-modal" class="md:w-3xl space-y-6">
        <div>
            <flux:heading size="lg" class="font-bold">
                {{ $form->station ? 'Edit Stasiun SPKLU' : 'Tambah Stasiun SPKLU Baru' }}
            </flux:heading>
            <flux:subheading class="text-xs text-zinc-500">
                Isi data lokasi charging station dan daftar colokan yang tersedia
            </flux:subheading>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{-- Basic Station Info --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Nama Stasiun *</flux:label>
                    <flux:input wire:model="form.name" placeholder="cth: SPKLU PLN Gambir" required />
                    <flux:error name="form.name" />
                </flux:field>

                <flux:field>
                    <flux:label>Operator</flux:label>
                    <flux:input wire:model="form.operator" placeholder="cth: PLN SPKLU / Shell / Starvo" />
                    <flux:error name="form.operator" />
                </flux:field>

                <div class="sm:col-span-2">
                    <flux:field>
                        <flux:label>Alamat Lengkap</flux:label>
                        <flux:input wire:model="form.address" placeholder="Jl. M.I. Ridwan Rais No.1, Gambir..." />
                        <flux:error name="form.address" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Latitude (Garis Lintang) *</flux:label>
                    <flux:input wire:model="form.latitude" type="number" step="0.0000001" placeholder="-6.178921" required />
                    <flux:error name="form.latitude" />
                </flux:field>

                <flux:field>
                    <flux:label>Longitude (Garis Bujur) *</flux:label>
                    <flux:input wire:model="form.longitude" type="number" step="0.0000001" placeholder="106.832412" required />
                    <flux:error name="form.longitude" />
                </flux:field>

                <flux:field>
                    <flux:label>Jam Operasional</flux:label>
                    <flux:input wire:model="form.opening_hours" placeholder="cth: 24 Jam / 06:00 - 22:00" />
                    <flux:error name="form.opening_hours" />
                </flux:field>

                <flux:field>
                    <flux:label>Catatan Tarif / Biaya</flux:label>
                    <flux:input wire:model="form.pricing_note" placeholder="cth: Rp 2.466 / kWh + PJU" />
                    <flux:error name="form.pricing_note" />
                </flux:field>

                <div class="sm:col-span-2">
                    <flux:field>
                        <flux:label>Catatan Tambahan</flux:label>
                        <flux:textarea wire:model="form.notes" rows="2" placeholder="Posisi charger dekat lobi, security standby..." />
                        <flux:error name="form.notes" />
                    </flux:field>
                </div>

                <div class="sm:col-span-2">
                    <flux:checkbox wire:model="form.is_favorite" label="Tandai sebagai Stasiun Favorit" />
                </div>
            </div>

            <flux:separator />

            {{-- Connectors Repeater Section --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="md" class="font-bold">Daftar Konektor / Colokan</flux:heading>
                        <flux:subheading class="text-xs text-zinc-500">
                            Aturan: Colokan GB/T otomatis default 'native', colokan lain default 'untested'
                        </flux:subheading>
                    </div>
                    <flux:button type="button" wire:click="addConnector" variant="filled" size="sm" icon="plus">
                        Tambah Colokan
                    </flux:button>
                </div>

                <div class="space-y-3">
                    @foreach ($form->connectors as $index => $connector)
                        <div class="p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 space-y-3" wire:key="connector-{{ $index }}">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500">
                                    Konektor #{{ $index + 1 }}
                                </span>
                                @if (count($form->connectors) > 1)
                                    <flux:button
                                        type="button"
                                        wire:click="removeConnector({{ $index }})"
                                        variant="ghost"
                                        size="xs"
                                        icon="x-mark"
                                        class="text-rose-500 hover:text-rose-600"
                                    >
                                        Hapus
                                    </flux:button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <flux:label class="text-xs">Arus *</flux:label>
                                    <flux:select wire:model="form.connectors.{{ $index }}.current_type" size="sm">
                                        <flux:select.option value="DC">DC (Fast Charging)</flux:select.option>
                                        <flux:select.option value="AC">AC (Normal Charging)</flux:select.option>
                                    </flux:select>
                                    <flux:error name="form.connectors.{{ $index }}.current_type" />
                                </div>

                                <div>
                                    <flux:label class="text-xs">Tipe Colokan *</flux:label>
                                    <flux:select wire:model.live="form.connectors.{{ $index }}.plug_type" size="sm">
                                        <flux:select.option value="GBT">GB/T (Native)</flux:select.option>
                                        <flux:select.option value="TYPE2">Type 2 (Mennekes)</flux:select.option>
                                        <flux:select.option value="CCS2">CCS Combo 2</flux:select.option>
                                        <flux:select.option value="CHADEMO">CHAdeMO</flux:select.option>
                                        <flux:select.option value="OTHER">Lainnya</flux:select.option>
                                    </flux:select>
                                    <flux:error name="form.connectors.{{ $index }}.plug_type" />
                                </div>

                                <div>
                                    <flux:label class="text-xs">Daya Listrik (kW) *</flux:label>
                                    <flux:input wire:model="form.connectors.{{ $index }}.power_kw" type="number" step="0.1" placeholder="cth: 60" size="sm" required />
                                    <flux:error name="form.connectors.{{ $index }}.power_kw" />
                                </div>

                                <div>
                                    <flux:label class="text-xs">Jumlah Unit *</flux:label>
                                    <flux:input wire:model="form.connectors.{{ $index }}.unit_count" type="number" min="1" size="sm" required />
                                    <flux:error name="form.connectors.{{ $index }}.unit_count" />
                                </div>

                                <div>
                                    <flux:label class="text-xs">Status Kompatibilitas *</flux:label>
                                    <flux:select wire:model="form.connectors.{{ $index }}.compat_status" size="sm">
                                        <flux:select.option value="native">Native (Direct)</flux:select.option>
                                        <flux:select.option value="adapter_ok">Adapter/Cable OK</flux:select.option>
                                        <flux:select.option value="untested">Untested (Belum Dites)</flux:select.option>
                                        <flux:select.option value="adapter_failed">Adapter Gagal</flux:select.option>
                                        <flux:select.option value="incompatible">Tidak Kompatibel</flux:select.option>
                                    </flux:select>
                                    <flux:error name="form.connectors.{{ $index }}.compat_status" />
                                </div>

                                <div>
                                    <flux:label class="text-xs">Terakhir Dites</flux:label>
                                    <flux:input wire:model="form.connectors.{{ $index }}.last_tested_at" type="date" size="sm" />
                                    <flux:error name="form.connectors.{{ $index }}.last_tested_at" />
                                </div>

                                <div class="sm:col-span-3">
                                    <flux:label class="text-xs">Catatan Adaptor / Kabel yang Digunakan</flux:label>
                                    <flux:input wire:model="form.connectors.{{ $index }}.adapter_note" placeholder="cth: Adaptor Type 2 to GB/T AC bawaan mobil" size="sm" />
                                    <flux:error name="form.connectors.{{ $index }}.adapter_note" />
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" class="bg-emerald-600 hover:bg-emerald-500 text-white">
                    Simpan Stasiun
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
