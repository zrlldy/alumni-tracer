{{-- resources/views/vendor/passkeys/components/authenticate.blade.php --}}
<div>
    @include('passkeys::components.partials.authenticateScript')

    <form id="passkey-login-form" method="POST" action="{{ route('passkeys.login') }}">
        @csrf
    </form>

    @if($message = session()->get('authenticatePasskey::message'))
        <div class="mb-4 flex items-start gap-2 rounded-lg bg-red-50 border border-red-300 px-3 py-2.5 text-sm text-red-700">
            <svg class="h-4 w-4 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ $message }}</span>
        </div>
    @endif

    <div onclick="authenticateWithPasskey()">
        {{ $slot }}
    </div>
</div>