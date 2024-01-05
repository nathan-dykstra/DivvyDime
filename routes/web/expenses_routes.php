<?php

use App\Http\Controllers\ExpensesController;
use Illuminate\Support\Facades\Route;

Route::get('/expenses', [ExpensesController::class, 'index'])->name('expenses');
Route::get('/expenses/create', [ExpensesController::class, 'create'])->name('expenses.create');
Route::post('/expenses', [ExpensesController::class, 'store'])->name('expenses.store');
/*Route::get('/expenses/{expense}', [ExpensesController::class, 'show'])->name('expenses.show');
Route::patch('/expenses/{expense}/update', [ExpensesController::class, 'update'])->name('expenses.update');
Route::delete('/expenses/{expense}/destroy', [ExpensesController::class, 'destroy'])->name('expenses.destroy');*/

Route::post('/expenses/search', [ExpensesController::class, 'search'])->name('expenses.search');
Route::post('/expenses/search-friends-to-include', [ExpensesController::class, 'searchFriendsToInclude'])->name('expenses.search-friends-to-include');
