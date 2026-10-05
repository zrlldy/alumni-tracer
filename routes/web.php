<?php

use App\Http\Controllers\GoogleAuthController;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPasskeys\Http\Controllers\AuthenticateUsingPasskeyController;
use Spatie\LaravelPasskeys\Http\Controllers\GeneratePasskeyAuthenticationOptionsController;

Route::get('/', function () {
    // return view('welcome');
    return redirect('/online');
});

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
