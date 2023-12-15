<?php

use App\Http\Controllers\GroupsController;
use Illuminate\Support\Facades\Route;

Route::get('/groups', [GroupsController::class, 'index'])->name('groups');
Route::get('/groups/create', [GroupsController::class, 'create'])->name('groups.create');
Route::post('/groups', [GroupsController::class, 'store'])->name('groups.store');
Route::get('/groups/{group_id}', [GroupsController::class, 'show'])->name('groups.show');

Route::get('/groups/{group}/settings', [GroupsController::class, 'settings'])->name('groups.settings');

Route::post('/groups/search', [GroupsController::class, 'search'])->name('groups.search');

Route::post('/groups/{group_id}/invite', [GroupsController::class, 'invite'])->name('groups.invite');
