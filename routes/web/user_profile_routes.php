<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserPreferenceController;
use Illuminate\Support\Facades\Route;

// User Profile
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

// User Preferences
Route::patch('/user-preferences', [UserPreferenceController::class, 'update'])->name('user-preferences.update');
