<div class="verification-prompt">
    <div class="verification-mark" aria-hidden="true">
        <x-filament::icon icon="heroicon-o-envelope" class="size-8" />
        <span><x-filament::icon icon="heroicon-m-check" class="size-3" /></span>
    </div>

    <h1>Check your inbox</h1>
    <p class="verification-lead">Confirm your email to continue. We've sent a verification link to:</p>

    <div class="verification-address">
        <x-filament::icon icon="heroicon-o-at-symbol" class="size-5 shrink-0" />
        <strong>{{ filament()->auth()->user()->getEmailForVerification() }}</strong>
    </div>

    <p class="verification-instructions">Open the email from Alumni Tracer and select <strong>Verify Email</strong> to continue.</p>

    {{ $this->resendNotificationAction }}

    <div class="verification-help">
        <x-filament::icon icon="heroicon-o-information-circle" class="size-5 shrink-0" />
        <p>Can't find the email? Check your spam or junk folder before requesting a new link.</p>
    </div>

    <form method="POST" action="{{ filament()->getLogoutUrl() }}" class="verification-switch">
        @csrf
        <span>Signed in with the wrong email?</span>
        <button type="submit">Sign out</button>
    </form>

    <x-filament-actions::modals />
</div>
