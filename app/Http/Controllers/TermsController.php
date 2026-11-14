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
        return view('legal.terms', ['document' => Terms::document(), 'acceptance' => false]);
    }

    public function edit(Request $request): View|RedirectResponse
    {
        if ($request->user()->fresh()?->hasAcceptedCurrentTerms()) {
            return redirect(Filament::getUrl());
        }

        return view('legal.terms', ['document' => Terms::document(), 'acceptance' => true]);
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
}
