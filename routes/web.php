<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ReportController;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/dashboard/top/comment', [CommentController::class, 'comment']);
    Route::post('/dashboard/top/approval', [CommentController::class, 'approval'])->name('approval');
    Route::get('/dashboard/top/destroy{id}', [CommentController::class, 'destroy'])->name('comment.destroy');
    Route::post('/dashboard/search', [AppController::class, 'search'])->name('dashboard.search');
    Route::get('/dashboard/search', [AppController::class, 'search'])->name('dashboard.search');
    Route::post('/dashboard/shop-search', [ShopController::class, 'shopSearch'])->name('dashboard.shop-search');
    Route::post('/dashboard/filter', [AppController::class, 'filter'])->name('dashboard.filter');
    Route::get('/dashboard/filter', [AppController::class, 'filter'])->name('dashboard.filter');
    Route::post('/dashboard/export', [AppController::class, 'export'])->name('dashboard.export');
    Route::post('/dashboard/report', [ReportController::class, 'report'])->name('dashboard.report');
    Route::resource('/dashboard/report', ReportController::class)->only([
        'index', 'show',
    ]);
    Route::resource('/dashboard/shop', ShopController::class)->only([
        'index',
    ]);
    Route::resource('/dashboard/top', AppController::class)->only([
        'index', 'show',
    ]);
});

Route::resource('/', GuestController::class);
Route::get('/{shop_id}', [GuestController::class, 'show'])->name('show');