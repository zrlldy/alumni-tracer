<div class="space-y-6">
    <x-filament::section>
        <x-slot name="heading">{{ $hasRecord ? 'My alumni information' : 'Complete my alumni information' }}</x-slot>
        <x-slot name="description">
            Keep your contact details, further studies, and employment history current.
            @if ($hasRecord)
                Contact the administrator to correct your name, Student ID, program, or graduation date.
            @endif
        </x-slot>

        @if ($saved)
            <p role="status" class="text-sm text-success-600 dark:text-success-400">Your alumni information has been saved.</p>
        @endif

        @error('data')
            <p role="alert" class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
        @enderror
    </x-filament::section>

    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        @if ($this->canSave())
            <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="save">
                {{ $hasRecord ? 'Save changes' : 'Save my information' }}
            </x-filament::button>
        @else
            <p class="text-sm text-gray-600 dark:text-gray-400">Your account has view-only access to this information.</p>
        @endif
    </form>

    <x-filament-actions::modals />
</div>
