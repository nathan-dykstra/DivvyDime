<?php

use App\Http\Controllers\PaymentsController;
use Illuminate\Support\Facades\Route;

Route::get('/payments/create', [PaymentsController::class, 'create'])->name('payments.create');
Route::post('/payments', [PaymentsController::class, 'store'])->name('payments.store');
Route::get('/payments/{expense_id}', [PaymentsController::class, 'show'])->name('payments.show');
//Route::get('/payment/{expense}/edit', [ExpensesController::class, 'edit'])->name('expenses.edit');
//Route::patch('/expenses/{expense}/update', [ExpensesController::class, 'update'])->name('expenses.update');
Route::delete('/payments/{expense}/destroy', [PaymentsController::class, 'destroy'])->name('payments.destroy');

//Route::post('/expenses/search', [ExpensesController::class, 'search'])->name('expenses.search');
//Route::post('/payments/search-friends-to-include', [PaymentsController::class, 'searchFriendsToInclude'])->name('payments.search-friends-to-include');
