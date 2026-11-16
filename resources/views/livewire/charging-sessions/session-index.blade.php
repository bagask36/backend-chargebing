<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" class="font-bold">Sesi Pengisian Daya</flux:heading>
            <flux:subheading class="text-zinc-500">
                Log histori pengisian baterai mobil listrik (GB/T DC & AC), konsumsi kWh, dan biaya
            </flux:subheading>
        </div>
        <div>
            <flux:button wire:click="create" variant="primary" icon="plus" class="bg-emerald-600 hover:bg-emerald-500 text-white font-medium">
                Catat Sesi Baru
            </flux:button>
        </div>
    </div>

    {{-- Filter Bar --}}
    <flux:card class="p-4 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <flux:select wire:model.live="charging_mode" placeholder="Mode Pengisian">
                    <flux:select.option value="all">Semua Mode</flux:select.option>
                    <flux:select.option value="DC">DC Fast Charging</flux:select.option>
                    <flux:select.option value="AC">AC Normal Charging</flux:select.option>
                </flux:select>
            </div>

            <div>
                <flux:select wire:model.live="station_filter" placeholder="Pilih Stasiun">
                    <flux:select.option value="all">Semua Stasiun</flux:select.option>
                    @foreach ($allStations as $st)
                        <flux:select.option value="{{ $st->id }}">{{ $st->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <flux:input wire:model.live="started_from" type="date" placeholder="Dari Tanggal" />
            </div>

            <div>
                <flux:input wire:model.live="started_until" type="date" placeholder="Sampai Tanggal" />
            </div>
        </div>

        {{-- PowerGrid Table --}}
        <div>
            <livewire:tables.charging-session-table
                :charging-mode="$charging_mode"
                :station-filter="$station_filter"
                :started-from="$started_from"
                :started-until="$started_until"
                :key="'sessions-table-'.$charging_mode.'-'.$station_filter.'-'.$started_from.'-'.$started_until"
            />
        </div>
    </flux:card>

    {{-- Create / Edit Modal --}}
    <flux:modal name="session-modal" class="w-full max-w-3xl lg:max-w-4xl space-y-6">
        <div>
            <flux:heading size="lg" class="font-bold">
                {{ $form->session ? 'Edit Sesi Pengisian' : 'Catat Sesi Pengisian Baru' }}
            </flux:heading>
            <flux:subheading class="text-xs text-zinc-500">
                Isi detail stasiun, waktu, konsumsi kWh, dan persentase baterai mobil listrik Anda
            </flux:subheading>
        </div>

        <form wire:submit="save" class="space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Vehicle Model --}}
                <div class="sm:col-span-2">
                    <flux:field>
                        <flux:label>Mobil Listrik (EV)</flux:label>
                        <flux:select wire:model.live="form.ev_model_id">
                            <flux:select.option value="">Pilih Mobil (Opsional / Default)</flux:select.option>
                            @foreach ($allEvModels as $car)
                                <flux:select.option value="{{ $car->id }}">
                                    {{ $car->brand }} {{ $car->model_name }} {{ $car->variant ? "({$car->variant})" : '' }} • {{ (float)$car->battery_capacity_kwh }} kWh ({{ $car->range_km }} km)
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="form.ev_model_id" />
                    </flux:field>
                </div>

                {{-- Station --}}
                <div class="sm:col-span-2">
                    <flux:field>
                        <flux:label>Stasiun SPKLU *</flux:label>
                        <flux:select wire:model.live="form.station_id" required>
                            <flux:select.option value="">Pilih Stasiun</flux:select.option>
                            @foreach ($allStations as $st)
                                <flux:select.option value="{{ $st->id }}">{{ $st->name }} ({{ $st->operator ?? 'Independen' }})</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="form.station_id" />
                    </flux:field>
                </div>

                {{-- Connector --}}
                <div>
                    <flux:field>
                        <flux:label>Colokan yang Digunakan</flux:label>
                        <flux:select wire:model.live="form.connector_id">
                            <flux:select.option value="">Pilih Colokan (Opsional)</flux:select.option>
                            @foreach ($availableConnectors as $conn)
                                <flux:select.option value="{{ $conn->id }}">
                                    {{ $conn->current_type->value }} {{ $conn->plug_type->value }} ({{ (int)$conn->power_kw }} kW) - {{ $conn->compat_status->label() }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="form.connector_id" />
                    </flux:field>
                </div>

                {{-- Mode --}}
                <div>
                    <flux:field>
                        <flux:label>Mode Pengisian *</flux:label>
                        <flux:select wire:model="form.charging_mode" required>
                            <flux:select.option value="DC">DC (Fast Charging)</flux:select.option>
                            <flux:select.option value="AC">AC (Normal Charging)</flux:select.option>
                        </flux:select>
                        <flux:error name="form.charging_mode" />
                    </flux:field>
                </div>

                {{-- Started At --}}
                <div>
                    <flux:field>
                        <flux:label>Waktu Mulai *</flux:label>
                        <flux:input wire:model="form.started_at" type="datetime-local" required />
                        <flux:error name="form.started_at" />
                    </flux:field>
                </div>

                {{-- Ended At --}}
                <div>
                    <flux:field>
                        <flux:label>Waktu Selesai</flux:label>
                        <flux:input wire:model="form.ended_at" type="datetime-local" />
                        <flux:error name="form.ended_at" />
                    </flux:field>
                </div>

                {{-- kWh --}}
                <div>
                    <flux:field>
                        <flux:label>Energi Terisi (kWh) *</flux:label>
                        <flux:input wire:model="form.kwh" type="number" step="0.01" min="0" placeholder="cth: 35.5" required />
                        <flux:error name="form.kwh" />
                    </flux:field>
                </div>

                {{-- Cost --}}
                <div>
                    <flux:field>
                        <flux:label>Total Biaya (Rp)</flux:label>
                        <flux:input wire:model="form.cost" type="number" step="100" min="0" placeholder="cth: 87500" />
                        <flux:error name="form.cost" />
                    </flux:field>
                </div>

                {{-- SoC Start --}}
                <div>
                    <flux:field>
                        <flux:label>State of Charge Mulai (%)</flux:label>
                        <flux:input wire:model.live.debounce.300ms="form.soc_start" type="number" min="0" max="100" placeholder="cth: 20" />
                        <flux:error name="form.soc_start" />
                    </flux:field>
                </div>

                {{-- SoC End --}}
                <div>
                    <flux:field>
                        <flux:label>State of Charge Selesai (%)</flux:label>
                        <flux:input wire:model.live.debounce.300ms="form.soc_end" type="number" min="0" max="100" placeholder="cth: 85" />
                        <flux:error name="form.soc_end" />
                    </flux:field>
                </div>

                {{-- Smart Charging Estimation Box --}}
                @php
                    $calcCar = $form->ev_model_id ? $allEvModels->firstWhere('id', $form->ev_model_id) : null;
                    $hasValidCalc = $calcCar && is_numeric($form->soc_start) && is_numeric($form->soc_end) && ((int)$form->soc_end > (int)$form->soc_start);
                @endphp
                @if ($hasValidCalc)
                    @php
                        $deltaPercent = (int)$form->soc_end - (int)$form->soc_start;
                        $calcKwh = round((float)$calcCar->battery_capacity_kwh * ($deltaPercent / 100), 2);
                        $calcCost = round($calcKwh * 2466);
                        $addedRange = round((float)$calcCar->range_km * ($deltaPercent / 100));
                    @endphp
                    <div class="sm:col-span-2 p-3.5 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                        <div class="space-y-0.5">
                            <div class="font-bold text-emerald-900 dark:text-emerald-200 flex items-center gap-1.5">
                                <span>⚡ Estimasi Baterai {{ $calcCar->brand }} {{ $calcCar->model_name }} ({{ (float)$calcCar->battery_capacity_kwh }} kWh):</span>
                            </div>
                            <div class="text-emerald-700 dark:text-emerald-300">
                                Kenaikan <strong>+{{ $deltaPercent }}%</strong> membutuhkan <strong>~{{ $calcKwh }} kWh</strong> (+{{ $addedRange }} km) • Estimasi biaya: <strong>Rp {{ number_format($calcCost, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                        <flux:button type="button" wire:click="applyEstimation" size="xs" variant="filled" class="bg-emerald-600 hover:bg-emerald-500 text-white shrink-0">
                            Terapkan ke Kolom kWh & Biaya
                        </flux:button>
                    </div>
                @endif

                {{-- Notes --}}
                <div class="sm:col-span-2">
                    <flux:field>
                        <flux:label>Catatan Pengisian</flux:label>
                        <flux:textarea wire:model="form.notes" rows="2" placeholder="Puncak daya 80 kW, lancar tanpa kendala..." />
                        <flux:error name="form.notes" />
                    </flux:field>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" class="bg-emerald-600 hover:bg-emerald-500 text-white">
                    Simpan Sesi
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>

