<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ZR Creation — Tailor for Gents
|--------------------------------------------------------------------------
| Order form, plus Dashboard and Reports sections. No auth.
*/

// Home: blank form (new order)
Route::get('/', [OrderController::class, 'index'])->name('orders.index');

// Dashboard (placeholder page — intentionally empty)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

// Reports (date range + status + payment filters)
Route::get('/report', [ReportController::class, 'index'])->name('report.index');

// Search (GET with ?q=...)
Route::get('/search', [OrderController::class, 'search'])->name('orders.search');

// Store new order
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

// Update existing order
Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');

// Delete order
Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

// Print view
Route::get('/orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
