<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuestController;

Route::resource('/', GuestController::class);
Route::get('/{id}', [GuestController::class, 'show'])->name('show');

require __DIR__.'/auth.php';
