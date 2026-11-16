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
    </flux:card>

    {{-- Sessions Table --}}
    <flux:card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Waktu Pengisian</flux:table.column>
                    <flux:table.column>Stasiun & Colokan</flux:table.column>
                    <flux:table.column>Mode</flux:table.column>
                    <flux:table.column>Energi</flux:table.column>
                    <flux:table.column>Biaya</flux:table.column>
                    <flux:table.column>SoC Baterai</flux:table.column>
                    <flux:table.column>Catatan</flux:table.column>
                    <flux:table.column class="text-right">Aksi</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($sessions as $session)
                        <flux:table.row :key="$session->id">
                            {{-- Time & Duration --}}
                            <flux:table.cell class="whitespace-nowrap">
                                <div class="font-medium text-zinc-900 dark:text-white">
                                    {{ $session->started_at?->format('d M Y, H:i') }}
                                </div>
                                <div class="text-xs text-zinc-400">
                                    @if ($session->ended_at)
                                        Durasi: {{ $session->started_at->diffInMinutes($session->ended_at) }} menit
                                    @else
                                        Sedang Berlangsung...
                                    @endif
                                </div>
                            </flux:table.cell>

                            {{-- Station & Connector --}}
                            <flux:table.cell>
                                <div class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $session->station->name }}
                                </div>
                                <div class="text-xs text-zinc-400">
                                    @if ($session->connector)
                                        {{ $session->connector->plug_type->value }} ({{ (int)$session->connector->power_kw }} kW) • {{ $session->connector->compat_status->label() }}
                                    @else
                                        {{ $session->station->operator ?? '-' }}
                                    @endif
                                </div>
                            </flux:table.cell>

                            {{-- Mode --}}
                            <flux:table.cell>
                                @if ($session->charging_mode->value === 'DC')
                                    <flux:badge color="emerald" size="sm">DC Fast</flux:badge>
                                @else
                                    <flux:badge color="sky" size="sm">AC Normal</flux:badge>
                                @endif
                            </flux:table.cell>

                            {{-- Energy (kWh) --}}
                            <flux:table.cell class="font-bold">
                                {{ number_format((float) $session->kwh, 2, ',', '.') }} kWh
                            </flux:table.cell>

                            {{-- Cost --}}
                            <flux:table.cell>
                                @if ($session->cost !== null)
                                    Rp {{ number_format((float) $session->cost, 0, ',', '.') }}
                                @else
                                    <span class="text-zinc-400 italic">Gratis / Belum Ada</span>
                                @endif
                            </flux:table.cell>

                            {{-- SoC --}}
                            <flux:table.cell>
                                @if ($session->soc_start !== null && $session->soc_end !== null)
                                    <div class="flex items-center gap-1.5 text-xs">
                                        <span class="text-zinc-400">{{ $session->soc_start }}%</span>
                                        <span>→</span>
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $session->soc_end }}%</span>
                                        <span class="text-zinc-400">(+{{ $session->soc_end - $session->soc_start }}%)</span>
                                    </div>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </flux:table.cell>

                            {{-- Notes --}}
                            <flux:table.cell class="text-xs text-zinc-500 max-w-xs truncate">
                                {{ $session->notes ?? '-' }}
                            </flux:table.cell>

                            {{-- Actions --}}
                            <flux:table.cell class="text-right">
                                <div class="inline-flex items-center gap-1">
                                    <flux:button
                                        wire:click="edit({{ $session->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="pencil-square"
                                        title="Edit"
                                    />
                                    <flux:button
                                        wire:click="delete({{ $session->id }})"
                                        wire:confirm="Yakin ingin menghapus sesi pengisian ini?"
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
                            <flux:table.cell colspan="8" class="text-center py-10 text-zinc-400">
                                Tidak ada data sesi pengisian yang cocok dengan filter.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        @if ($sessions->hasPages())
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
                {{ $sessions->links() }}
            </div>
        @endif
    </flux:card>

    {{-- Create / Edit Modal --}}
    <flux:modal name="session-modal" class="md:w-2xl space-y-6">
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
                        <flux:input wire:model="form.soc_start" type="number" min="0" max="100" placeholder="cth: 20" />
                        <flux:error name="form.soc_start" />
                    </flux:field>
                </div>

                {{-- SoC End --}}
                <div>
                    <flux:field>
                        <flux:label>State of Charge Selesai (%)</flux:label>
                        <flux:input wire:model="form.soc_end" type="number" min="0" max="100" placeholder="cth: 85" />
                        <flux:error name="form.soc_end" />
                    </flux:field>
                </div>

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
