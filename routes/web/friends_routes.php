<?php

use App\Http\Controllers\FriendsController;
use Illuminate\Support\Facades\Route;

Route::get('/friends', [FriendsController::class, 'index'])->name('friends');
Route::post('/friends', [FriendsController::class, 'invite'])->name('friends.invite');
Route::get('/friends/{friend_id}', [FriendsController::class, 'show'])->name('friends.show');
Route::post('/friends/search', [FriendsController::class, 'search'])->name('friends.search');

Route::post('/friends/requests/{request}/accept', [FriendsController::class, 'accept'])->name('friends.accept');
Route::delete('/friends/requests/{request}/deny', [FriendsController::class, 'deny'])->name('friends.deny');
