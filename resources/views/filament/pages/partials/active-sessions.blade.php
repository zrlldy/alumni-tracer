{{-- resources/views/filament/pages/partials/active-sessions.blade.php --}}

@php
    $sessions = $this->getActiveSessions();
@endphp

<div class="space-y-2.5">
    @forelse ($sessions as $session)
        @php($agent = \App\Support\DeviceParser::parse($session->user_agent, $session->client_hints ?? []))

        <div class="group relative flex flex-col gap-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/[0.03] p-4 transition-colors hover:border-gray-300 dark:hover:border-white/20 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-start gap-3 min-w-0">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-50 dark:bg-white/5 text-gray-400 dark:text-gray-500 ring-1 ring-gray-200 dark:ring-white/10">
                    @if ($agent['isMobile'])
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="7" y="2" width="10" height="20" rx="2"/><line x1="11" y1="18" x2="13" y2="18"/>
                        </svg>
                    @else
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="13" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                    @endif
                </div>

                <div class="min-w-0 pt-0.5">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-medium text-gray-950 dark:text-white">
                            {{ $agent['label'] }}
                        </p>
                        @if ($session->is_current_device)
                            <span class="inline-flex items-center gap-1 rounded-full bg-success-50 dark:bg-success-500/10 px-2 py-0.5 text-xs font-medium text-success-700 dark:text-success-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                                This device
                            </span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        {{ $session->ip_address }} <span class="mx-1 text-gray-300 dark:text-gray-600">&middot;</span> Active {{ $session->last_active }}
                    </p>
                </div>
            </div>

            <div class="shrink-0 sm:pl-2">
                @if ($session->is_current_device)
                    <button
                        type="button"
                        wire:click="mountAction('logoutSession', { sessionId: '{{ $session->id }}' })"
                       class="inline-flex w-full sm:w-auto items-center justify-center gap-1.5 rounded-lg border border-danger-200 dark:border-danger-500/20 px-3 py-1.5 text-xs font-medium text-danger-600 dark:text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-500/10 active:scale-[0.97] transition-all"
                    >
                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Log out
                    </button>
                @else
                    <button
                        type="button"
                        wire:click="mountAction('revokeSession', { sessionId: '{{ $session->id }}' })"
                        class="inline-flex w-full sm:w-auto items-center justify-center gap-1.5 rounded-lg border border-danger-200 dark:border-danger-500/20 px-3 py-1.5 text-xs font-medium text-danger-600 dark:text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-500/10 active:scale-[0.97] transition-all"
                    >
                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Log out
                    </button>
                @endif
            </div>
        </div>
    @empty
        <div class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-gray-200 dark:border-white/10 py-8 text-center">
            <svg class="h-8 w-8 text-gray-300 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="13" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">No active sessions</p>
        </div>
    @endforelse

    @if ($sessions->count() > 1)
        <div class="pt-1">
            <button
                type="button"
                wire:click="mountAction('revokeSession')"
                class="text-xs font-medium text-danger-600 dark:text-danger-400 hover:text-danger-500 hover:underline underline-offset-2"
            >
                Log out all other devices
            </button>
        </div>
    @endif
</div>