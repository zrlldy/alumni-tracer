{{-- resources/views/filament/pages/partials/trusted-devices.blade.php --}}

@php
    $devices = $this->getTrustedDevices();
@endphp

<div class="space-y-2.5">
    @forelse ($devices as $device)
        @php($hints = ['model' => $device->device_model, 'platform' => $device->device_platform, 'platformVersion' => $device->device_platform_version])
        @php($agent = \App\Support\DeviceParser::parse($device->user_agent, $hints))
        @php($isCurrent = $device->id === session('current_trusted_device_id'))
        @php($isExpiringSoon = $device->expires_at->diffInDays(now()) <= 3)

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
                        @if ($isCurrent)
                            <span class="inline-flex items-center gap-1 rounded-full bg-success-50 dark:bg-success-500/10 px-2 py-0.5 text-xs font-medium text-success-700 dark:text-success-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                                This device
                            </span>
                        @endif
                        @if ($isExpiringSoon)
                            <span class="inline-flex items-center rounded-full bg-warning-50 dark:bg-warning-500/10 px-2 py-0.5 text-xs font-medium text-warning-700 dark:text-warning-400">
                                Expiring soon
                            </span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        {{ $device->ip_address }} <span class="mx-1 text-gray-300 dark:text-gray-600">&middot;</span>
                        Trusted {{ $device->created_at->diffForHumans() }} <span class="mx-1 text-gray-300 dark:text-gray-600">&middot;</span>
                        Expires {{ $device->expires_at->diffForHumans() }}
                    </p>
                </div>
            </div>

            <div class="shrink-0 sm:pl-2">
                <button
                    type="button"
                    wire:click="mountAction('revokeDevice', { deviceId: {{ $device->id }} })"
                    class="inline-flex w-full sm:w-auto items-center justify-center gap-1.5 rounded-lg border border-danger-200 dark:border-danger-500/20 px-3 py-1.5 text-xs font-medium text-danger-600 dark:text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-500/10 active:scale-[0.97] transition-all"
                >
                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    Remove
                </button>
            </div>
        </div>
    @empty
        <div class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-gray-200 dark:border-white/10 py-8 text-center">
            <svg class="h-8 w-8 text-gray-300 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">No trusted devices yet</p>
        </div>
    @endforelse

    @if ($devices->count() > 1)
        <div class="pt-1">
            <button
                type="button"
                wire:click="mountAction('revokeOtherDevices')"
                class="text-xs font-medium text-danger-600 dark:text-danger-400 hover:text-danger-500 hover:underline underline-offset-2"
            >
                Remove all other trusted devices
            </button>
        </div>
    @endif
</div>