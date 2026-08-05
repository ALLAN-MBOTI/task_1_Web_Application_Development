<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvoiceController;

/**
 * ============================================================================
 * Application Web Routes
 * ============================================================================
 * Defines the public entry point (Login) and protected functional routes.
 * Uses Laravel's built-in 'auth' middleware to restrict invoice features.
 * ============================================================================
 */

// Public Auth Routes (Base URL loads Login)
// Authentication Routes
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected ERP Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/invoice', [InvoiceController::class, 'index'])->name('invoice.index');
    Route::get('/api/customers/search', [InvoiceController::class, 'searchCustomers']);
    Route::get('/api/search-sales-employees', [InvoiceController::class, 'searchSalesEmployees'])->name('invoice.search-sales-employees');
    Route::post('/invoice/store', [InvoiceController::class, 'store'])->name('invoice.store');
});


