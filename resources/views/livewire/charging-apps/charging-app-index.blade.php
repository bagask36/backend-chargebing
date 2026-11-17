<div class="space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">
                📱 Master Aplikasi Charging
            </flux:heading>
            <flux:subheading class="text-xs text-zinc-500 mt-1">
                Kelola daftar aplikasi operator SPKLU (PLN Mobile, Voltron, Terra Charge, E-Charge, dsb.) beserta skema deep link iOS dan tautan Store.
            </flux:subheading>
        </div>

        <flux:button wire:click="create" variant="primary" icon="plus" class="bg-emerald-600 hover:bg-emerald-500 text-white font-medium">
            Tambah Aplikasi
        </flux:button>
    </div>

    {{-- Filter Card --}}
    <flux:card class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama aplikasi, identifier slug..."
                icon="magnifying-glass"
                clearable
            />

            <flux:select wire:model.live="statusFilter">
                <flux:select.option value="all">Semua Status</flux:select.option>
                <flux:select.option value="active">Hanya Aktif</flux:select.option>
                <flux:select.option value="inactive">Nonaktif</flux:select.option>
            </flux:select>
        </div>

        {{-- Table --}}
        <div>
            <flux:table :paginate="$chargingApps">
                <flux:table.columns>
                    <flux:table.column>Nama & Identifier</flux:table.column>
                    <flux:table.column>URL Scheme (iOS/Android)</flux:table.column>
                    <flux:table.column>Toko Aplikasi</flux:table.column>
                    <flux:table.column>Stasiun SPKLU</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Aksi</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($chargingApps as $app)
                        <flux:table.row :key="$app->id">
                            {{-- Nama & Identifier --}}
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <div class="size-9 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold flex items-center justify-center border border-emerald-500/20 text-sm">
                                        ⚡
                                    </div>
                                    <div>
                                        <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ $app->name }}
                                        </div>
                                        <div class="text-xs text-zinc-400 font-mono">
                                            {{ $app->slug }}
                                        </div>
                                    </div>
                                </div>
                            </flux:table.cell>

                            {{-- URL Scheme --}}
                            <flux:table.cell>
                                @if ($app->scheme_url)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                        {{ $app->scheme_url }}
                                    </span>
                                @else
                                    <span class="text-xs text-zinc-400 italic">Belum diatur</span>
                                @endif
                            </flux:table.cell>

                            {{-- Toko Aplikasi Links --}}
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    @if ($app->app_store_url)
                                        <a href="{{ $app->app_store_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-[11px] font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                            <span>App Store</span>
                                            <flux:icon icon="arrow-top-right-on-square" class="size-3" />
                                        </a>
                                    @endif

                                    @if ($app->play_store_url)
                                        <a href="{{ $app->play_store_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400 hover:underline">
                                            <span>Play Store</span>
                                            <flux:icon icon="arrow-top-right-on-square" class="size-3" />
                                        </a>
                                    @endif

                                    @if (! $app->app_store_url && ! $app->play_store_url)
                                        <span class="text-xs text-zinc-400">-</span>
                                    @endif
                                </div>
                            </flux:table.cell>

                            {{-- Stasiun Terkait --}}
                            <flux:table.cell>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                    <span>📍</span>
                                    <span>{{ $app->stations_count }} SPKLU</span>
                                </span>
                            </flux:table.cell>

                            {{-- Status Aktif --}}
                            <flux:table.cell>
                                <button
                                    type="button"
                                    wire:click="toggleStatus({{ $app->id }})"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold transition {{ $app->is_active ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/25' : 'bg-zinc-500/15 text-zinc-500 hover:bg-zinc-500/25' }}"
                                >
                                    <span class="size-1.5 rounded-full {{ $app->is_active ? 'bg-emerald-500' : 'bg-zinc-400' }}"></span>
                                    <span>{{ $app->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </button>
                            </flux:table.cell>

                            {{-- Aksi --}}
                            <flux:table.cell align="end">
                                <div class="flex items-center justify-end gap-1">
                                    <flux:button wire:click="edit({{ $app->id }})" size="sm" variant="ghost" icon="pencil" title="Edit Aplikasi" />
                                    <flux:button
                                        wire:click="delete({{ $app->id }})"
                                        wire:confirm="Yakin ingin menghapus aplikasi charging ini? Stasiun yang terhubung akan beralih ke mode auto-match."
                                        size="sm"
                                        variant="ghost"
                                        icon="trash"
                                        class="text-red-500 hover:text-red-600"
                                        title="Hapus"
                                    />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center py-8 text-zinc-500">
                                Tidak ada aplikasi charging yang cocok dengan pencarian.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>

    {{-- Create / Edit Modal --}}
    <flux:modal name="charging-app-modal" class="w-full max-w-2xl space-y-6">
        <div>
            <flux:heading size="lg" class="font-bold">
                {{ $form->chargingApp ? 'Edit Aplikasi Charging' : 'Tambah Aplikasi Charging Baru' }}
            </flux:heading>
            <flux:subheading class="text-xs text-zinc-500">
                Setup identitas aplikasi operator, URL scheme untuk deep link iOS, dan tautan toko aplikasi
            </flux:subheading>
        </div>

        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Nama Aplikasi --}}
                <flux:field>
                    <flux:label>Nama Aplikasi *</flux:label>
                    <flux:input wire:model.live.debounce.300ms="form.name" placeholder="cth: PLN Mobile / Voltron" required />
                    <flux:error name="form.name" />
                </flux:field>

                {{-- Identifier Slug --}}
                <flux:field>
                    <flux:label>Identifier Slug *</flux:label>
                    <flux:input wire:model="form.slug" placeholder="cth: pln-mobile / voltron" required />
                    <flux:error name="form.slug" />
                </flux:field>
            </div>

            {{-- URL Scheme --}}
            <flux:field>
                <flux:label>URL Scheme Deep Link (iOS / Android)</flux:label>
                <flux:input wire:model="form.scheme_url" placeholder="cth: plnmobile:// atau voltron://" />
                <flux:description class="text-[11px] text-zinc-500">
                    Skema URL kustom yang digunakan oleh aplikasi iOS ChargeBing untuk langsung meluncurkan aplikasi pihak ketiga saat pengguna menekan tombol "Buka App".
                </flux:description>
                <flux:error name="form.scheme_url" />
            </flux:field>

            {{-- App Store URL --}}
            <flux:field>
                <flux:label>Tautan Apple App Store (iOS Fallback)</flux:label>
                <flux:input wire:model="form.app_store_url" type="url" placeholder="cth: https://apps.apple.com/id/app/pln-mobile/id1296796967" />
                <flux:description class="text-[11px] text-zinc-500">
                    Tautan App Store resmi jika aplikasi belum terpasang di iPhone pengguna.
                </flux:description>
                <flux:error name="form.app_store_url" />
            </flux:field>

            {{-- Play Store URL --}}
            <flux:field>
                <flux:label>Tautan Google Play Store (Android Fallback)</flux:label>
                <flux:input wire:model="form.play_store_url" type="url" placeholder="cth: https://play.google.com/store/apps/details?id=com.icon.pln123" />
                <flux:error name="form.play_store_url" />
            </flux:field>

            {{-- Status Aktif --}}
            <flux:field>
                <div class="flex items-center gap-2 pt-2">
                    <flux:checkbox wire:model="form.is_active" label="Aplikasi Aktif (Dapat dipilih di dropdown stasiun SPKLU)" />
                </div>
                <flux:error name="form.is_active" />
            </flux:field>

            {{-- Actions --}}
            <div class="flex justify-end gap-2 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" class="bg-emerald-600 hover:bg-emerald-500 text-white font-medium">
                    {{ $form->chargingApp ? 'Simpan Perubahan' : 'Tambahkan Aplikasi' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>

