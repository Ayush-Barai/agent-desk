<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::livewire('/', 'landing-page')->name('home');

Route::livewire('/login', 'auth.login-page')->name('login');
Route::livewire('/register', 'auth.register-page')->name('register');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::livewire('/dashboard', 'dashboard-page')
        ->name('dashboard');

    Route::livewire('/tickets/create', 'tickets.create-ticket')->name('tickets.create');
    Route::livewire('/tickets/{ticketId}', 'tickets.ticket-detail')
    ->name('tickets.detail');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home');
    })->name('logout');

});