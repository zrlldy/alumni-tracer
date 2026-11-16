<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Terms;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TermsController extends Controller
{
    public function show(): View
    {
        return view('legal.terms', [
            'document' => Terms::document(),
            'acceptance' => false,
            'googleSignup' => false,
        ]);
    }


    public function edit(Request $request): View|RedirectResponse
    {
        if ($request->user()->fresh()?->hasAcceptedCurrentTerms()) {
            return redirect(Filament::getUrl());
        }

        return view('legal.terms', [
            'document' => Terms::document(),
            'acceptance' => true,
            'googleSignup' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'terms' => ['accepted'],
            'version' => ['required', Rule::in([Terms::VERSION])],
        ], [
            'terms.accepted' => 'Please agree to the Terms and Conditions to continue.',
            'version.in' => 'The terms have changed. Reload this page and review the current version.',
        ]);

        /** @var User|null $user */
        $user = $request->user()->fresh();
        abort_unless($user, 403);
        $user->acceptCurrentTerms();

        return redirect(Filament::getUrl());
    }

    public function googleSignup(): View
    {
        return view('legal.terms', [
            'document' => Terms::document(),
            'acceptance' => true,
            'googleSignup' => true,
        ]);
    }

    public function acceptGoogleSignup(Request $request): RedirectResponse
    {
        $request->validate([
            'terms' => ['accepted'],
            'version' => ['required', Rule::in([Terms::VERSION])],
        ], [
            'terms.accepted' => 'Please agree to the Terms and Conditions before continuing with Google.',
            'version.in' => 'The Terms and Conditions have changed. Please review the latest version.',
        ]);

        session([
            'google_signup_terms_version' => Terms::VERSION,
            'google_auth_intent' => 'signup',
        ]);

        return redirect()->route('auth.google.signup.redirect');
    }
}
