<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'ChargeBing - EV Charging Tracker' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-screen bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 antialiased">
    <flux:sidebar sticky stashable class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-800">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <div class="flex items-center gap-2.5 px-2 py-1">
            <div class="h-9 w-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-lg border border-emerald-500/30">
                ⚡
            </div>
            <div>
                <span class="font-bold text-lg tracking-tight">ChargeBing</span>
                <span class="block text-[11px] text-zinc-400 font-medium -mt-1">EV Personal Tracker</span>
            </div>
        </div>

        <flux:separator class="my-2" />

        <flux:navlist variant="outline">
            <flux:navlist.item icon="home" href="/" :current="request()->is('/')" wire:navigate>
                Dashboard
            </flux:navlist.item>
            <flux:navlist.item icon="map-pin" href="/stations" :current="request()->is('stations*')" wire:navigate>
                Stasiun SPKLU
            </flux:navlist.item>
            <flux:navlist.item icon="bolt" href="/sessions" :current="request()->is('sessions*')" wire:navigate>
                Sesi Pengisian
            </flux:navlist.item>
        </flux:navlist>

        <flux:spacer />

        <flux:navlist variant="outline">
            <flux:navlist.item icon="device-phone-mobile" href="/docs/api" target="_blank">
                REST API Docs
            </flux:navlist.item>
        </flux:navlist>

        <flux:separator class="my-2" />

        <div class="px-2 py-1 flex items-center justify-between">
            <div class="truncate">
                <span class="block text-sm font-semibold truncate">{{ auth()->user()?->name ?? 'Pemilik' }}</span>
                <span class="block text-xs text-zinc-400 truncate">{{ auth()->user()?->email }}</span>
            </div>
            <form method="POST" action="/logout">
                @csrf
                <flux:button type="submit" variant="ghost" size="sm" icon="arrow-right-start-on-rectangle" title="Keluar" />
            </form>
        </div>
    </flux:sidebar>

    <flux:header class="lg:hidden border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900">
        <flux:sidebar.toggle icon="bars-2" />
        <div class="flex items-center gap-2 ml-2">
            <span class="text-base font-bold">ChargeBing</span>
        </div>
        <flux:spacer />
        <form method="POST" action="/logout">
            @csrf
            <flux:button type="submit" variant="ghost" size="sm" icon="arrow-right-start-on-rectangle" />
        </form>
    </flux:header>

    <flux:main class="p-6 lg:p-8 max-w-7xl mx-auto w-full">
        {{ $slot }}
    </flux:main>

    <flux:toast />
    @fluxScripts
</body>
</html>
