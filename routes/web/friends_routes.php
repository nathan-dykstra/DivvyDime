<?php

use App\Http\Controllers\FriendsController;
use Illuminate\Support\Facades\Route;

Route::get('/friends', [FriendsController::class, 'index'])->name('friends');

Route::post('/friends', [FriendsController::class, 'invite'])->name('friends.invite');
