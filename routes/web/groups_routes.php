<?php

use App\Http\Controllers\GroupsController;
use Illuminate\Support\Facades\Route;

Route::get('/groups', [GroupsController::class, 'index'])->name('groups');
Route::get('/groups/get-groups', [GroupsController::class, 'getGroups'])->name('groups.get-groups');
Route::get('/groups/create', [GroupsController::class, 'create'])->name('groups.create');
Route::post('/groups', [GroupsController::class, 'store'])->name('groups.store');
Route::get('/groups/{group_id}', [GroupsController::class, 'show'])->name('groups.show');
Route::get('/groups/{group_id}/get-group-expenses', [GroupsController::class, 'getGroupExpenses'])->name('groups.get-group-expenses');
Route::delete('/groups/{group}/destroy', [GroupsController::class, 'destroy'])->name('groups.destroy');
Route::get('/groups/{group}/settings', [GroupsController::class, 'settings'])->name('groups.settings');
Route::patch('/groups/{group}/update', [GroupsController::class, 'update'])->name('groups.update');

Route::post('/groups/{group}/search-friends-to-invite', [GroupsController::class, 'searchFriendsToInvite'])->name('groups.search-friends-to-invite');
Route::post('/groups/{group}/invite', [GroupsController::class, 'invite'])->name('groups.invite');

Route::post('/groups/{group}/remove-member', [GroupsController::class, 'removeMember'])->name('groups.remove-member');
Route::post('/groups/{group}/leave-group', [GroupsController::class, 'leaveGroup'])->name('groups.leave-group');

Route::post('/groups/invites/accept', [GroupsController::class, 'accept'])->name('groups.accept');
Route::delete('/groups/invites/reject', [GroupsController::class, 'reject'])->name('groups.reject');
