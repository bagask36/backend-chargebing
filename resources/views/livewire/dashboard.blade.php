<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" class="font-bold">Ringkasan Pengisian EV</flux:heading>
            <flux:subheading class="text-zinc-500">
                Data statistik stasiun dan pemakaian daya mobil listrik (GB/T AC & DC)
            </flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:button href="/sessions" variant="primary" icon="bolt" wire:navigate class="bg-emerald-600 hover:bg-emerald-500 text-white">
                Catat Sesi Baru
            </flux:button>
            <flux:button href="/stations" variant="filled" icon="plus" wire:navigate>
                Tambah Stasiun
            </flux:button>
        </div>
    </div>

    {{-- Metrics Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Stations --}}
        <flux:card class="space-y-2">
            <div class="flex items-center justify-between text-zinc-500">
                <span class="text-xs font-semibold uppercase tracking-wider">Total Stasiun</span>
                <flux:icon icon="map-pin" class="size-4 text-emerald-500" />
            </div>
            <div class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">
                {{ $totalStations }}
            </div>
            <div class="text-xs text-zinc-500">
                Tersimpan di database pribadi
            </div>
        </flux:card>

        {{-- Total Sesi --}}
        <flux:card class="space-y-2">
            <div class="flex items-center justify-between text-zinc-500">
                <span class="text-xs font-semibold uppercase tracking-wider">Total Sesi Pengisian</span>
                <flux:icon icon="bolt" class="size-4 text-amber-500" />
            </div>
            <div class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">
                {{ $totalSessions }}
            </div>
            <div class="text-xs text-zinc-500">
                Riwayat pengisian tercatat
            </div>
        </flux:card>

        {{-- Energi Bulan Ini (kWh) --}}
        <flux:card class="space-y-2">
            <div class="flex items-center justify-between text-zinc-500">
                <span class="text-xs font-semibold uppercase tracking-wider">Energi {{ $currentMonthName }}</span>
                <flux:icon icon="chart-bar" class="size-4 text-cyan-500" />
            </div>
            <div class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">
                {{ number_format($monthTotalKwh, 1, ',', '.') }} <span class="text-sm font-normal text-zinc-400">kWh</span>
            </div>
            <div class="flex items-center gap-2 pt-1 text-xs">
                <span class="inline-flex items-center gap-1 font-medium text-emerald-600 dark:text-emerald-400">
                    <span class="size-1.5 rounded-full bg-emerald-500"></span>
                    DC: {{ number_format($monthDcKwh, 1, ',', '.') }} kWh
                </span>
                <span class="text-zinc-400">•</span>
                <span class="inline-flex items-center gap-1 font-medium text-sky-600 dark:text-sky-400">
                    <span class="size-1.5 rounded-full bg-sky-500"></span>
                    AC: {{ number_format($monthAcKwh, 1, ',', '.') }} kWh
                </span>
            </div>
        </flux:card>

        {{-- Biaya Bulan Ini --}}
        <flux:card class="space-y-2">
            <div class="flex items-center justify-between text-zinc-500">
                <span class="text-xs font-semibold uppercase tracking-wider">Biaya {{ $currentMonthName }}</span>
                <flux:icon icon="banknotes" class="size-4 text-violet-500" />
            </div>
            <div class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">
                <span class="text-lg font-normal text-zinc-400">Rp</span> {{ number_format($monthTotalCost, 0, ',', '.') }}
            </div>
            <div class="flex items-center gap-2 pt-1 text-xs">
                <span class="text-zinc-500">
                    DC: Rp {{ number_format($monthDcCost, 0, ',', '.') }} ({{ $monthDcCount }}x)
                </span>
                <span class="text-zinc-400">•</span>
                <span class="text-zinc-500">
                    AC: Rp {{ number_format($monthAcCost, 0, ',', '.') }} ({{ $monthAcCount }}x)
                </span>
            </div>
        </flux:card>
    </div>

    {{-- 5 Sesi Terakhir --}}
    <flux:card class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg" class="font-bold">5 Sesi Pengisian Terakhir</flux:heading>
                <flux:subheading class="text-xs text-zinc-500">Aktivitas pengisian daya terbaru Anda</flux:subheading>
            </div>
            <flux:button href="/sessions" variant="ghost" size="sm" icon:trailing="arrow-right" wire:navigate>
                Lihat Semua
            </flux:button>
        </div>

        <flux:separator />

        <div class="overflow-x-auto">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Tanggal & Waktu</flux:table.column>
                    <flux:table.column>Stasiun SPKLU</flux:table.column>
                    <flux:table.column>Mode</flux:table.column>
                    <flux:table.column>Energi (kWh)</flux:table.column>
                    <flux:table.column>Biaya (Rp)</flux:table.column>
                    <flux:table.column>State of Charge (SoC)</flux:table.column>
                    <flux:table.column>Catatan</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($recentSessions as $session)
                        <flux:table.row :key="$session->id">
                            <flux:table.cell class="whitespace-nowrap font-medium">
                                {{ $session->started_at?->format('d M Y, H:i') }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="font-medium text-zinc-900 dark:text-white">
                                    {{ $session->station->name }}
                                </div>
                                <div class="text-xs text-zinc-400">
                                    {{ $session->station->operator ?? 'Independen' }}
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                @if ($session->charging_mode->value === 'DC')
                                    <flux:badge color="emerald" size="sm">DC Fast</flux:badge>
                                @else
                                    <flux:badge color="sky" size="sm">AC Normal</flux:badge>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="font-semibold">
                                {{ number_format((float) $session->kwh, 2, ',', '.') }} kWh
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $session->cost !== null ? 'Rp ' . number_format((float) $session->cost, 0, ',', '.') : '-' }}
                            </flux:table.cell>

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

                            <flux:table.cell class="text-xs text-zinc-500 max-w-xs truncate">
                                {{ $session->notes ?? '-' }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="7" class="text-center py-6 text-zinc-400">
                                Belum ada riwayat sesi pengisian.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>
</div>
