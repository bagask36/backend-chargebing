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

        <div>
            <livewire:tables.recent-charging-session-table />
        </div>
    </flux:card>
</div>

