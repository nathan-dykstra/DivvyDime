<?php

use App\Http\Controllers\PaymentsController;
use Illuminate\Support\Facades\Route;

Route::get('/payments/create', [PaymentsController::class, 'create'])->name('payments.create');
Route::post('/payments', [PaymentsController::class, 'store'])->name('payments.store');
Route::get('/payments/{expense_id}', [PaymentsController::class, 'show'])->name('payments.show');
Route::get('/payments/{expense}/edit', [PaymentsController::class, 'edit'])->name('payments.edit');
Route::patch('/payments/{expense}/update', [PaymentsController::class, 'update'])->name('payments.update');
Route::delete('/payments/{expense}/destroy', [PaymentsController::class, 'destroy'])->name('payments.destroy');

Route::post('/payments/reject', [PaymentsController::class, 'rejectPayment'])->name('payments.reject');
Route::post('/payments/confirm', [PaymentsController::class, 'confirmPayment'])->name('payments.confirm');
