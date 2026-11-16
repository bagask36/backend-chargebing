<div class="space-y-6">
    {{-- Top Action Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" class="font-bold">Peta & Titik Charging Station</flux:heading>
            <flux:subheading class="text-zinc-500">
                Visualisasi titik SPKLU mobil listrik (GB/T AC & DC), deteksi jarak, dan pencarian lokasi charger
            </flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:button wire:click="create" variant="primary" icon="plus" class="bg-emerald-600 hover:bg-emerald-500 text-white font-medium">
                Tambah Stasiun
            </flux:button>
        </div>
    </div>

    {{-- Interactive Leaflet Map & Search Box --}}
    <flux:card class="p-4 space-y-4 shadow-lg border-zinc-200 dark:border-zinc-800">
        <div
            x-data="stationsMap({{ Js::from($allMapStations) }})"
            class="space-y-3"
        >
            {{-- Search Bar & Location Controls --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <form @submit.prevent="searchLocation" class="flex-1 flex items-center gap-2 max-w-lg">
                    <div class="relative flex-1" @click.outside="showSuggestions = false">
                        <flux:input
                            x-model="searchQuery"
                            @input.debounce.350ms="onSearchInput"
                            @focus="if (suggestions.length > 0) showSuggestions = true"
                            placeholder="Cari lokasi di peta (cth: Rest Area KM 57, Senayan, Gambir)..."
                            icon="magnifying-glass"
                        />

                        {{-- Suggestions Dropdown --}}
                        <div
                            x-show="showSuggestions && suggestions.length > 0"
                            x-transition
                            class="absolute left-0 right-0 top-full mt-1.5 z-[1050] bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-2xl max-h-64 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-700/60"
                            style="display: none;"
                        >
                            <template x-for="(item, idx) in suggestions" :key="idx">
                                <div
                                    @mousedown.prevent="selectSuggestion(item)"
                                    class="p-2.5 hover:bg-emerald-50 dark:hover:bg-zinc-700/60 cursor-pointer transition-colors flex items-start gap-2.5 text-left"
                                >
                                    <span class="text-emerald-500 mt-0.5 text-sm">📍</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-semibold text-zinc-900 dark:text-white truncate" x-text="item.name"></p>
                                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400 line-clamp-1" x-text="item.display_name"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <flux:button type="submit" variant="filled" size="sm" icon="magnifying-glass">
                        <span x-show="!isSearching">Cari</span>
                        <span x-show="isSearching" style="display: none;">Mencari...</span>
                    </flux:button>
                </form>

                <div class="flex items-center gap-2">
                    <flux:button
                        type="button"
                        @click="locateMe"
                        variant="subtle"
                        size="sm"
                        icon="map-pin"
                        class="text-emerald-600 dark:text-emerald-400 font-medium"
                    >
                        Lokasi Mobil Saya
                    </flux:button>
                </div>
            </div>

            {{-- Legend Indicators --}}
            <div class="flex flex-wrap items-center gap-3 text-xs pt-1 pb-1 text-zinc-600 dark:text-zinc-400 border-b border-zinc-100 dark:border-zinc-800/60">
                <span class="font-semibold text-zinc-700 dark:text-zinc-300">Status Port:</span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="size-2.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50"></span>
                    GB/T Native (Langsung Colok)
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="size-2.5 rounded-full bg-sky-500 shadow-sm shadow-sky-500/50"></span>
                    Adaptor OK (Type 2 / CCS2)
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="size-2.5 rounded-full bg-amber-500 shadow-sm shadow-amber-500/50"></span>
                    Untested (Belum Dites)
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="size-2.5 rounded-full bg-rose-500 shadow-sm shadow-rose-500/50"></span>
                    Incompatible
                </span>
            </div>

            {{-- Map Container --}}
            <div wire:ignore class="relative w-full h-[400px] sm:h-[450px] rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-800 z-0">
                <div x-ref="mapContainer" class="w-full h-full min-h-[400px]"></div>
            </div>
        </div>
    </flux:card>

    {{-- Filter & Search Bar for Table --}}
    <flux:card class="p-4 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Filter tabel nama/alamat..."
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

        {{-- PowerGrid Table --}}
        <div>
            <livewire:tables.station-table
                :search="$search"
                :current-type="$current_type"
                :plug-type="$plug_type"
                :compat-status="$compat_status"
                :key="'stations-table-'.$search.'-'.$current_type.'-'.$plug_type.'-'.$compat_status"
            />
        </div>
    </flux:card>

    {{-- Create / Edit Modal with Repeater Connectors and Location Picker Map --}}
    <flux:modal name="station-modal" class="w-full max-w-4xl lg:max-w-5xl space-y-6">
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

                {{-- Interactive Map Location Picker --}}
                <div class="sm:col-span-2 space-y-2">
                    <div
                        x-data="locationPickerMap({{ $form->latitude ?? -6.2088 }}, {{ $form->longitude ?? 106.8456 }})"
                        class="p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/70 dark:bg-zinc-900/70 space-y-2.5"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 relative z-30">
                            <div>
                                <flux:label class="text-xs font-bold text-zinc-800 dark:text-zinc-200">
                                    📍 Pilih Titik Charger di Peta
                                </flux:label>
                                <p class="text-[11px] text-zinc-500">
                                    Klik di peta atau cari nama lokasi untuk meletakkan pin koordinat.
                                </p>
                            </div>

                            <div class="relative flex-1 max-w-sm w-full" @click.outside="showSuggestions = false">
                                <div class="flex items-center gap-1.5">
                                    <div class="relative flex-1">
                                        <flux:input
                                            x-model="pickerSearch"
                                            @input.debounce.300ms="onPickerInput"
                                            @focus="if (suggestions.length > 0) showSuggestions = true"
                                            placeholder="Ketik alamat/nama gedung..."
                                            size="sm"
                                            @keydown.enter.prevent="if (suggestions.length > 0) { selectSuggestion(suggestions[0]); } else { searchPickerLocation(); }"
                                            @keydown.escape="showSuggestions = false"
                                        />
                                        {{-- Loading spinner --}}
                                        <div x-show="isSearching" class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" style="display: none;">
                                            <svg class="animate-spin h-3.5 w-3.5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <flux:button
                                        type="button"
                                        @click="searchPickerLocation"
                                        variant="filled"
                                        size="sm"
                                    >
                                        Set
                                    </flux:button>
                                </div>

                                {{-- Suggestions Dropdown in Modal --}}
                                <div
                                    x-show="showSuggestions && suggestions.length > 0"
                                    x-transition
                                    class="absolute left-0 right-0 top-full mt-1.5 z-[1050] bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-700/60"
                                    style="display: none;"
                                >
                                    <template x-for="(item, idx) in suggestions" :key="idx">
                                        <div
                                            @mousedown.prevent="selectSuggestion(item)"
                                            class="p-2.5 hover:bg-emerald-50 dark:hover:bg-zinc-700/80 cursor-pointer transition-colors flex items-start gap-2 text-left"
                                        >
                                            <span class="text-emerald-500 mt-0.5 text-xs">📍</span>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-semibold text-zinc-900 dark:text-white truncate" x-text="item.name"></p>
                                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 line-clamp-1" x-text="item.display_name"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div wire:ignore class="w-full h-72 sm:h-80 rounded-lg overflow-hidden border border-zinc-300 dark:border-zinc-700 relative z-0">
                            <div x-ref="pickerMapContainer" class="w-full h-full min-h-[288px]"></div>
                        </div>
                    </div>
                </div>

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
