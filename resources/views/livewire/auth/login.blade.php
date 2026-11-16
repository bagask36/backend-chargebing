<flux:card class="space-y-6 shadow-xl border-zinc-200/80 dark:border-zinc-800">
    <div>
        <flux:heading size="lg" class="text-center font-bold">Masuk ke Akun Pemilik</flux:heading>
        <flux:subheading class="text-center text-sm text-zinc-500 mt-1">
            Pantau dan kelola data pengisian mobil listrik Anda
        </flux:subheading>
    </div>

    <form wire:submit="login" class="space-y-5">
        <flux:field>
            <flux:label>Email</flux:label>
            <flux:input
                wire:model="email"
                type="email"
                placeholder="admin@chargebing.local"
                required
                autofocus
            />
            <flux:error name="email" />
        </flux:field>

        <flux:field>
            <flux:label>Password</flux:label>
            <flux:input
                wire:model="password"
                type="password"
                placeholder="••••••••"
                required
            />
            <flux:error name="password" />
        </flux:field>

        <div class="flex items-center justify-between">
            <flux:checkbox wire:model="remember" label="Ingat saya" />
        </div>

        <flux:button type="submit" variant="primary" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-medium">
            Masuk
        </flux:button>
    </form>
</flux:card>
