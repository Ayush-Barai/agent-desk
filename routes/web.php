<?php

declare(strict_types=1);

use App\Http\Controllers\AttachmentDownloadController;
use App\Http\Controllers\ProfileController;
use App\Models\Ticket;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get('/', fn (): Factory|View => view('welcome'));

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', fn (): Factory|View => view('dashboard'))->name('dashboard');

    // Requester routes
    Route::middleware('role:requester,agent,admin')
        ->prefix('tickets')
        ->name('requester.tickets.')
        ->group(function (): void {
            Route::get('/', fn (): Factory|View => view('requester.tickets.index'))->name('index');
            Route::get('/create', fn (): Factory|View => view('requester.tickets.create'))->name('create');
            Route::get('/{ticket}', fn (Ticket $ticket): Factory|View => view('requester.tickets.show', ['ticket' => $ticket]))->name('show');
        });

    // Agent routes
    Route::middleware('role:agent,admin')
        ->prefix('agent')
        ->name('agent.')
        ->group(function (): void {
            Route::get('/triage', fn (): Factory|View => view('agent.triage.index'))->name('triage.index');
            Route::get('/tickets', fn (): Factory|View => view('agent.tickets.index'))->name('tickets.index');
            Route::get('/tickets/{ticket}', fn (Ticket $ticket): Factory|View => view('agent.tickets.show', ['ticket' => $ticket]))->name('tickets.show');
        });

    // Attachment download
    Route::get('/attachments/{attachment}/download', AttachmentDownloadController::class)->name('attachments.download');

    // Admin routes
    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function (): void {
            Route::get('/users', fn (): Factory|View => view('admin.users.index'))->name('users.index');
        });
});

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
