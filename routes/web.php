<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/transactions/export', [TransactionController::class, 'export'])
        ->name('transactions.export');

    Route::resource('transactions', TransactionController::class)
        ->except(['show']);
});

require __DIR__ . '/auth.php';