<div class="inline-flex items-center justify-end gap-1 w-full">
    <flux:button
        wire:click="$parent.edit({{ $station->id }})"
        variant="ghost"
        size="sm"
        icon="pencil-square"
        title="Edit"
    />
    <flux:button
        wire:click="$parent.delete({{ $station->id }})"
        wire:confirm="Yakin ingin menghapus stasiun ini?"
        variant="ghost"
        size="sm"
        icon="trash"
        class="text-rose-600 hover:text-rose-700 dark:text-rose-400"
        title="Hapus"
    />
</div>

