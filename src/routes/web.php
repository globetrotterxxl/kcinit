<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\KeycloakAuthController;

// AUTHENTICATION
Route::get('/login', fn () => redirect()->route('auth.keycloak.redirect'))
    ->name('login');

Route::get('/auth/keycloak/redirect', [KeycloakAuthController::class, 'redirectToProvider'])
    ->name('auth.keycloak.redirect');

Route::get('/auth/keycloak/callback', [KeycloakAuthController::class, 'handleProviderCallback'])
    ->name('auth.keycloak.callback');

Route::post('/logout', [KeycloakAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


