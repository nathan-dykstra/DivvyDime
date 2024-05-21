<?php

use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

Route::post('/images/upload-profile', [ImageController::class, 'uploadProfileImage'])->name('images.upload-profile');
Route::delete('/images/delete-profile', [ImageController::class, 'deleteProfileImage'])->name('images.delete-profile');

Route::post('/images/upload-expense/{expense}', [ImageController::class, 'uploadExpenseImages'])->name('images.upload-expense');
Route::delete('/images/delete-expense/{expense_image_id}', [ImageController::class, 'deleteExpenseImage'])->name('images.delete-expense');

Route::post('/images/upload-group/{group}', [ImageController::class, 'uploadGroupImage'])->name('images.upload-group');
Route::delete('/images/delete-group/{group}', [ImageController::class, 'deleteGroupImage'])->name('images.delete-group');
