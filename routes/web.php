<?php

use App\Http\Controllers\CustomerController;
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

// Search Customer page: find a customer by number to VIEW & UPDATE their
// orders only. No new order can ever be created/saved from this page.
Route::get('/orders/lookup', [OrderController::class, 'searchOrder'])->name('orders.searchOrder');

// Update Order page: find an order by SUIT NUMBER (or phone) to VIEW &
// UPDATE it directly. No new order can ever be created/saved from here.
Route::get('/orders/update', [OrderController::class, 'updateOrder'])->name('orders.updateOrder');

// Customer lookup by customer number (id) — read-only, used by order form
// to preview an existing customer's name/last measurements for reference.
Route::get('/customers/{id}/lookup', [CustomerController::class, 'lookup'])->name('customers.lookup')->whereNumber('id');

// Store new order
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

// Update existing order
Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');

// Delete order
Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

// Print view
Route::get('/orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');