<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;



// Unified order page: add a new order, update an existing one, or search
// an old customer's history — all from one screen (q = suit#/phone, cn = customer#).
Route::get('/', [OrderController::class, 'index'])->name('orders.index');

// Dashboard 
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

// Reports (date range + status + payment filters)
Route::get('/report', [ReportController::class, 'index'])->name('report.index');

// Customer lookup 
Route::get('/customers/{id}/lookup', [CustomerController::class, 'lookup'])->name('customers.lookup')->whereNumber('id');

// View all current customers
Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');

// Store new order
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

// Update existing order
Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');

// Delete order
Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

// Print view
Route::get('/orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');