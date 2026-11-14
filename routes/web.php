<?php

use App\Http\Controllers\AuthenticateUsingPasskeyController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPasskeys\Http\Controllers\GeneratePasskeyAuthenticationOptionsController;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::get('/terms', [TermsController::class, 'show'])->name('terms.show');
Route::middleware('auth')->group(function (): void {
    Route::get('/terms/accept', [TermsController::class, 'edit'])->name('terms.accept');
    Route::post('/terms/accept', [TermsController::class, 'store'])
        ->middleware('throttle:10,1')->name('terms.store');
});

Route::post('/', [WelcomeController::class, 'submit'])->name('welcome.submit');

Route::passkeys();

Route::prefix('passkeys')->group(function () {
    Route::get('authentication-options', GeneratePasskeyAuthenticationOptionsController::class)
        ->name('passkeys.authentication_options');

    Route::post('authenticate', AuthenticateUsingPasskeyController::class)
        ->name('passkeys.login');
});

Route::get('auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('auth/google/relink', [GoogleAuthController::class, 'relink'])->name('auth.google.relink')->middleware('auth');
Route::get('auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
