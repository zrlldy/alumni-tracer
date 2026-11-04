{{-- resources/views/filament/pages/partials/account-picker.blade.php --}}
<div class="space-y-3">
    {{-- <h2 class="text-lg font-semibold text-white text-center">Who's signing in?</h2> --}}

    <div class="relative">
        <div class="space-y-2 {{ count($accounts) > 3 ? 'max-h-[15.5rem] overflow-y-auto pr-1' : '' }}">
            @foreach ($accounts as $userId => $account)
                <div class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-3 hover:border-gray-300 transition-colors">
                    <button
                        type="button"
                        wire:click="selectAccount('{{ $account['email'] }}')"
                        class="flex flex-1 items-center gap-3 min-w-0 text-left"
                    >
                        @if ($account['avatar'])
                            <img src="{{ $account['avatar'] }}" alt="" class="h-10 w-10 rounded-full object-cover shrink-0">
                        @else
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-700 font-semibold">
                                {{ strtoupper(substr($account['name'], 0, 1)) }}
                            </div>
                        @endif

                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-950 truncate">{{ $account['name'] }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $account['email'] }}</p>
                        </div>
                    </button>

                    <button
                        type="button"
                        wire:click="mountAction('forgetAccount', { email: '{{ $account['email'] }}' })"
                        class="shrink-0 rounded-full p-1.5 text-red-500 hover:bg-red-50 hover:text-red-700 transition-all"
                        title="Remove from this device"
                    >
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
            @endforeach
        </div>

        @if (count($accounts) > 3)
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-6 bg-gradient-to-t from-white to-transparent"></div>
        @endif
    </div>

    <button
        type="button"
        wire:click="showDifferentAccountForm"
        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-blue-700 hover:bg-gray-50 transition-colors"
    >
        Use a different account
    </button>
</div>

<x-filament-actions::modals />