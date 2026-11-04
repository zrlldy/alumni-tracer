<?php
// app/Http/Controllers/AuthenticateUsingPasskeyController.php

namespace App\Http\Controllers;

use App\Support\ClientHints;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Spatie\LaravelPasskeys\Actions\FindPasskeyToAuthenticateAction;
use Spatie\LaravelPasskeys\Http\Controllers\AuthenticateUsingPasskeyController as BaseController;
use Spatie\LaravelPasskeys\Http\Requests\AuthenticateUsingPasskeysRequest;
use Spatie\LaravelPasskeys\Support\Config;

class AuthenticateUsingPasskeyController extends BaseController
{
    public function __invoke(AuthenticateUsingPasskeysRequest $request)
    {
        $passkeyOptions = Session::pull('passkey-authentication-options');

        if (blank($passkeyOptions)) {
            return $this->invalidPasskeyResponse();
        }

        $findAuthenticatableUsingPasskey = Config::getAction(
            'find_passkey',
            FindPasskeyToAuthenticateAction::class
        );

        $passkey = $findAuthenticatableUsingPasskey->execute(
            $request->input('start_authentication_response'),
            $passkeyOptions,
        );

        if (! $passkey) {
            return $this->invalidPasskeyResponse();
        }

        /** @var Authenticatable $authenticatable */
        $authenticatable = $passkey->authenticatable;

        if (! $authenticatable) {
            return $this->invalidPasskeyResponse();
        }

        if (
            $authenticatable instanceof FilamentUser
            && ! $authenticatable->canAccessPanel(Filament::getCurrentOrDefaultPanel())
        ) {
            return $this->unauthorizedPasskeyResponse();
        }

        $this->logInAuthenticatable($authenticatable, $request->boolean('remember'));

        ClientHints::rememberInSession(request());

        $this->firePasskeyEvent($passkey, $request);

        return $this->validPasskeyResponse($request);
    }

    protected function invalidPasskeyResponse(): RedirectResponse
    {
        Notification::make()
            ->title('Passkey sign-in failed')
            ->body('We couldn\'t verify that passkey. Please try again or use your password.')
            ->danger()
            ->send();

        return back();
    }

    protected function unauthorizedPasskeyResponse(): RedirectResponse
    {
        Notification::make()
            ->title('Access denied')
            ->body('This account does not have access to this panel.')
            ->danger()
            ->send();

        return back();
    }

    protected function validPasskeyResponse(Request $request): RedirectResponse
    {
        $url = Session::has('passkeys.redirect')
            ? Session::pull('passkeys.redirect')
            : Filament::getUrl();

        return redirect()->intended($url);
    }
}