{{-- resources/views/vendor/passkeys/livewire/passkeys.blade.php --}}
<div class="space-y-6 text-[#003049] dark:text-[#f8fafc]">
    <section aria-labelledby="add-passkey-heading" class="rounded-[22px] border border-[#003049]/[0.12] bg-white/95 p-5 dark:border-white/10 dark:bg-[#0a1e2a]/95 sm:p-6">
        <div class="mb-6 flex items-start gap-3.5">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-[13px] border border-[#F77F00]/20 bg-[#FCBF49]/15 text-[#003049] dark:text-[#FCBF49]" aria-hidden="true">
                <x-heroicon-o-key class="size-[22px]" />
            </span>
            <div class="min-w-0 pt-0.5">
                <h3 id="add-passkey-heading" class="text-base font-bold tracking-tight">Add a new passkey</h3>
                <p class="mt-1 text-sm leading-6 text-[#64748b] dark:text-[#94a3b8]">Register this device for passwordless login.</p>
            </div>
        </div>

        <form id="passkeyForm" wire:submit="validatePasskeyProperties" novalidate class="flex flex-col gap-3 sm:flex-row sm:items-start">
            <div class="min-w-0 flex-1">
                <label for="name" class="mb-2 block text-xs font-semibold">Passkey name <span class="font-normal text-[#64748b] dark:text-[#94a3b8]">(required)</span></label>
                <input
                    id="name"
                    type="text"
                    wire:model="name"
                    autocomplete="off"
                    required
                    placeholder="e.g. My iPhone, Work Laptop"
                    aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                    aria-describedby="passkey-name-help{{ $errors->has('name') ? ' passkey-name-error' : '' }}"
                    class="block min-h-[50px] w-full min-w-0 rounded-[13px] border border-[#003049]/[0.16] bg-white px-4 py-3 text-sm text-[#003049] shadow-sm placeholder:text-[#64748b] focus:border-[#F77F00] focus:outline-none focus:ring-2 focus:ring-[#F77F00]/20 aria-[invalid=true]:border-[#D62828] dark:border-white/15 dark:bg-[#0c2330] dark:text-[#f8fafc] dark:placeholder:text-[#94a3b8]"
                />
                <p id="passkey-name-help" class="mt-2 text-xs leading-5 text-[#64748b] dark:text-[#94a3b8]">Choose a name you'll recognize later.</p>
                @error('name')
                    <p id="passkey-name-error" role="alert" class="mt-1 text-xs font-medium leading-5 text-[#D62828] dark:text-red-300">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="validatePasskeyProperties"
                class="inline-flex min-h-[50px] w-full cursor-pointer items-center justify-center gap-2 rounded-[13px] border border-transparent bg-gradient-to-br from-[#F77F00] to-[#FCBF49] px-5 py-3 text-sm font-bold text-[#003049] shadow-[0_8px_20px_rgba(247,127,0,0.16)] transition hover:brightness-105 active:translate-y-px focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#003049] disabled:cursor-wait disabled:opacity-60 motion-reduce:transition-none dark:focus-visible:outline-[#FCBF49] sm:mt-6 sm:w-auto"
            >
                <span wire:loading.remove wire:target="validatePasskeyProperties" aria-hidden="true"><x-heroicon-o-plus class="size-[18px]" /></span>
                <svg wire:loading wire:target="validatePasskeyProperties" class="size-[18px] animate-spin motion-reduce:animate-none" aria-hidden="true" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" opacity="0.25" />
                    <path d="M12 3a9 9 0 0 1 9 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
                <span>Add passkey</span>
            </button>
        </form>
    </section>

    <section aria-labelledby="saved-passkeys-heading" class="space-y-3">
        <div class="px-1">
            <h3 id="saved-passkeys-heading" class="text-sm font-bold">Your passkeys</h3>
            <p class="mt-1 text-xs leading-5 text-[#64748b] dark:text-[#94a3b8]">Manage the passkeys connected to your account.</p>
        </div>

        <ul role="list" class="space-y-3">
            @forelse ($passkeys as $passkey)
                <li wire:key="passkey-{{ $passkey->id }}" class="flex min-w-0 flex-col gap-4 rounded-[18px] border border-[#003049]/[0.12] bg-white/95 p-4 dark:border-white/10 dark:bg-[#0a1e2a]/95 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-[#003049]/[0.05] text-[#003049] dark:bg-white/5 dark:text-[#FCBF49]" aria-hidden="true">
                            <x-heroicon-o-key class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <h4 class="break-words text-sm font-semibold leading-5 [overflow-wrap:anywhere]">{{ $passkey->name }}</h4>
                            <div class="mt-1.5 flex flex-wrap gap-x-3 gap-y-1 text-xs leading-5 text-[#64748b] dark:text-[#94a3b8]">
                                <span>Created <time datetime="{{ $passkey->created_at->toIso8601String() }}">{{ $passkey->created_at->format('M j, Y \a\t g:i A') }}</time></span>
                                <span>{{ $passkey->last_used_at ? 'Last used '.$passkey->last_used_at->diffForHumans() : 'Never used' }}</span>
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        wire:click="mountAction('deletePasskey', { passkeyId: {{ $passkey->id }} })"
                        wire:loading.attr="disabled"
                        wire:target="mountAction"
                        aria-label="Remove passkey: {{ $passkey->name }}"
                        class="inline-flex min-h-11 w-full shrink-0 cursor-pointer items-center justify-center gap-2 rounded-xl border border-[#D62828]/20 px-3.5 py-2 text-xs font-semibold text-[#D62828] transition-colors hover:border-[#D62828]/40 hover:bg-[#D62828]/5 active:bg-[#D62828]/10 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#D62828] disabled:cursor-wait disabled:opacity-50 motion-reduce:transition-none dark:border-red-300/20 dark:text-red-300 dark:hover:bg-red-300/10 dark:focus-visible:outline-red-300 sm:w-auto"
                    >
                        <x-heroicon-o-trash class="size-4" aria-hidden="true" />
                        Remove
                    </button>
                </li>
            @empty
                <li class="flex flex-col items-center rounded-[18px] border border-dashed border-[#003049]/20 bg-[#003049]/[0.02] px-6 py-9 text-center dark:border-white/15 dark:bg-white/[0.02]">
                    <span class="mb-3 flex size-12 items-center justify-center rounded-2xl bg-[#FCBF49]/15 text-[#003049] dark:text-[#FCBF49]" aria-hidden="true">
                        <x-heroicon-o-key class="size-6" />
                    </span>
                    <p class="text-sm font-semibold">No passkeys added yet</p>
                    <p class="mt-1 max-w-xs text-xs leading-6 text-[#64748b] dark:text-[#94a3b8]">Add your first passkey using the form above.</p>
                </li>
            @endforelse
        </ul>
    </section>

    <x-filament-actions::modals />
</div>
@include('passkeys::livewire.partials.createScript')
