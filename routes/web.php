<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\QuoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'index'])->name('home');
Route::post('/consultas', [PublicController::class, 'storeInquiry'])->middleware('throttle:5,1')->name('inquiries.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/acceso', [AuthController::class, 'create'])->name('login');
    Route::post('/acceso', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function (): void {
    Route::post('/salir', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('clientes', ClientController::class)->except(['show'])->parameters(['clientes' => 'client']);
    Route::post('/presupuestos/{quote}/pagos', [PaymentController::class, 'store'])->name('presupuestos.pagos.store');
    Route::get('/presupuestos/{quote}/pagos/pdf', [PaymentController::class, 'pdf'])->name('presupuestos.pagos.pdf');
    Route::get('/presupuestos/{quote}/pagos/{payment}/editar', [PaymentController::class, 'edit'])->name('presupuestos.pagos.edit');
    Route::put('/presupuestos/{quote}/pagos/{payment}', [PaymentController::class, 'update'])->name('presupuestos.pagos.update');
    Route::delete('/presupuestos/{quote}/pagos/{payment}', [PaymentController::class, 'destroy'])->name('presupuestos.pagos.destroy');
    Route::get('/presupuestos/{quote}/pdf', [QuoteController::class, 'pdf'])->name('presupuestos.pdf');
    Route::resource('presupuestos', QuoteController::class)->parameters(['presupuestos' => 'quote']);
});
