<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\ImageAdminController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReservationAdminController;
use App\Http\Controllers\UserAdminController;
use App\Http\Controllers\VenueController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {
    // Route::post('/dashboard/top/comment', [CommentController::class, 'comment']);
    // Route::post('/dashboard/top/approval', [CommentController::class, 'approval'])->name('approval');
    // Route::get('/dashboard/top/destroy{id}', [CommentController::class, 'destroy'])->name('comment.destroy');
    // Route::post('/dashboard/top/distribution', [AppController::class, 'distribution'])->name('dashboard.distribution');
    // Route::post('/dashboard/search', [AppController::class, 'search'])->name('dashboard.search');
    // Route::get('/dashboard/search', [AppController::class, 'search'])->name('dashboard.search');
    // Route::post('/dashboard/shop-search', [ShopController::class, 'shopSearch'])->name('dashboard.shop-search');
    // Route::post('/dashboard/filter', [AppController::class, 'filter'])->name('dashboard.filter');
    // Route::get('/dashboard/filter', [AppController::class, 'filter'])->name('dashboard.filter');
    // Route::post('/dashboard/export', [AppController::class, 'export'])->name('dashboard.export');
    // Route::post('/dashboard/report', [ReportController::class, 'report'])->name('dashboard.report');

    // Route::resource('/dashboard/report', ReportController::class)->only([
    //     'index', 'show',
    // ]);
    Route::get('/dashboard/report', [ReportController::class, 'index'])->name('report.index');
    Route::resource('/dashboard/article', ArticleController::class)->only([
        'index', 'store', 'edit', 'update', 'destroy',
    ]);
    Route::post('/dashboard/article/{article}/status', [ArticleController::class, 'updateStatus'])->name('article.status.update');
    Route::post('/dashboard/top/{article}/copy', [AppController::class, 'copy'])->name('top.copy');
    Route::resource('/dashboard/top', AppController::class)->only([
        'index', 'show', 'store',
    ]);
    Route::resource('/dashboard/venue', VenueController::class)->only([
        'index', 'create', 'store', 'show', 'update', 'destroy',
    ]);
    Route::resource('/dashboard/images', ImageAdminController::class)->only([
        'index', 'store', 'destroy',
    ]);
    Route::resource('/dashboard/users', UserAdminController::class)->only([
        'index',
    ]);
    Route::get('/dashboard/reservations', [ReservationAdminController::class, 'index'])->name('reservations.index');
    Route::get('/dashboard/reservations/export', [ReservationAdminController::class, 'export'])->name('reservations.export');
    Route::get('/dashboard/reservations/{reservation}', [ReservationAdminController::class, 'show'])->name('reservations.show');
    Route::patch('/dashboard/reservations/{reservation}/staff', [ReservationAdminController::class, 'updateStaff'])->name('reservations.updateStaff');
});

Route::get('/dashboard/article/images/{image}', [ArticleController::class, 'image'])->name('article.image');
Route::get('/dashboard/venue/images/{venue}', [VenueController::class, 'image'])->name('venue.image');
Route::post('/reservations', [GuestController::class, 'store'])->name('reservation.store');
Route::get('/p/{token}', [GuestController::class, 'showByToken'])->name('show.public');
Route::resource('/', GuestController::class);
Route::get('/{id}', [GuestController::class, 'show'])->name('show');