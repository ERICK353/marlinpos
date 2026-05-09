<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit="create">
            {{ $this->form }}
            
            <div class="mt-6 flex justify-end">
                <x-filament::button type="submit" size="lg" class="bg-primary-600 hover:bg-primary-500 shadow-lg shadow-primary-500/30">
                    Provision Shop Workspace
                </x-filament::button>
            </div>
        </form>

    </div>
</x-filament-panels::page>
