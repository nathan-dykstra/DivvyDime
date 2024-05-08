<?php

use App\Http\Controllers\PaymentsController;
use Illuminate\Support\Facades\Route;

Route::get('/payments/create', [PaymentsController::class, 'create'])->name('payments.create');
Route::post('/payments', [PaymentsController::class, 'store'])->name('payments.store');
Route::get('/payments/{payment_id}', [PaymentsController::class, 'show'])->name('payments.show');
Route::get('/payments/{payment}/edit', [PaymentsController::class, 'edit'])->name('payments.edit');
Route::patch('/payments/{payment}/update', [PaymentsController::class, 'update'])->name('payments.update');
Route::delete('/payments/{payment}/destroy', [PaymentsController::class, 'destroy'])->name('payments.destroy');

Route::post('/payments/get-balances-with-user', [PaymentsController::class, 'getBalancesWithUser'])->name('payments.get-balances-with-user');
Route::post('/payments/reject', [PaymentsController::class, 'rejectPayment'])->name('payments.reject');
Route::post('/payments/confirm', [PaymentsController::class, 'confirmPayment'])->name('payments.confirm');
