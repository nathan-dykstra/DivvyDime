<?php

use App\Http\Controllers\ExpensesController;
use Illuminate\Support\Facades\Route;

Route::get('/expenses', [ExpensesController::class, 'index'])->name('expenses');
Route::get('/expenses/get-expenses', [ExpensesController::class, 'getExpenses'])->name('expenses.get-expenses');
Route::get('/expenses/create', [ExpensesController::class, 'create'])->name('expenses.create');
Route::post('/expenses', [ExpensesController::class, 'store'])->name('expenses.store');
Route::get('/expenses/{expense_id}', [ExpensesController::class, 'show'])->name('expenses.show');
Route::get('/expenses/{expense}/edit', [ExpensesController::class, 'edit'])->name('expenses.edit');
Route::patch('/expenses/{expense}/update', [ExpensesController::class, 'update'])->name('expenses.update');
Route::delete('/expenses/{expense}/destroy', [ExpensesController::class, 'destroy'])->name('expenses.destroy');

Route::post('/expenses/search-friends-to-include', [ExpensesController::class, 'searchFriendsToInclude'])->name('expenses.search-friends-to-include');
Route::post('/expenses/get-expense-group-details', [ExpensesController::class, 'getExpenseGroupDetails'])->name('expenses.get-expense-group-details');
Route::patch('/expenses/{expense}/update-note', [ExpensesController::class, 'updateNote'])->name('expenses.update-note');
