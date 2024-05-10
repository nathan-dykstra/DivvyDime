<?php

use App\Http\Controllers\ActivityController;
use Illuminate\Support\Facades\Route;

Route::get('/activity', [ActivityController::class, 'index'])->name('activity');
Route::delete('/activity/{notification_id}/delete', [ActivityController::class, 'delete'])->name('activity.delete');
Route::delete('/activity/clear-all', [ActivityController::class, 'clearAll'])->name('activity.clear-all');

Route::get('/activity/get-updated-notifications', [ActivityController::class, 'getUpdatedNotifications'])->name('activity.get-updated-notifications');
Route::post('/activity/search', [ActivityController::class, 'search'])->name('activity.search');
