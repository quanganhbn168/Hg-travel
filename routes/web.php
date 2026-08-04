<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DestinationController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TourCategoryController;
use App\Http\Controllers\Admin\TourController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ContactController as FrontendContactController;
use App\Http\Controllers\Frontend\PostController as FrontendPostController;
use App\Http\Controllers\Frontend\ServiceController as FrontendServiceController;
use App\Http\Controllers\Frontend\SlugController;
use App\Http\Controllers\Frontend\TourController as FrontendTourController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/tours', [FrontendTourController::class, 'index'])->name('tours.index');
Route::get('/tours/danh-muc/{category:slug}', [FrontendTourController::class, 'category'])->name('tours.category');
Route::get('/tours/{tour:slug}', [FrontendTourController::class, 'show'])->name('tours.show');
Route::get('/dich-vu', [FrontendServiceController::class, 'index'])->name('services.index');
Route::get('/cam-nang', [FrontendPostController::class, 'index'])->name('posts.index');
Route::get('/lien-he', [FrontendContactController::class, 'create'])->name('contact');
Route::post('/lien-he', [FrontendContactController::class, 'store'])->middleware('throttle:10,1')->name('contact.store');

Route::middleware('guest:admin')->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::post('/admin/logout', [AuthController::class, 'destroy'])->middleware('auth:admin')->name('admin.logout');

Route::prefix('admin')->name('admin.')->middleware(['auth:admin', 'admin'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('media', [MediaController::class, 'index'])->name('media.index');
    Route::get('media/list', [MediaController::class, 'list'])->name('media.list');
    Route::post('media/upload/temp', [MediaController::class, 'uploadTemp'])->name('media.upload.temp');
    Route::post('media/upload/editor', [MediaController::class, 'uploadEditor'])->name('media.upload.editor');
    Route::get('settings/general', [SettingController::class, 'general'])->name('settings.general');
    Route::put('settings/general', [SettingController::class, 'updateGeneral'])->name('settings.general.update');
    Route::resource('destinations', DestinationController::class)->except('show');
    Route::resource('tour-categories', TourCategoryController::class)->except('show')->parameters(['tour-categories' => 'tourCategory']);
    Route::resource('tours', TourController::class)->except('show');
    Route::resource('bookings', BookingController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::resource('pages', PageController::class)->except('show');
    Route::resource('post-categories', PostCategoryController::class)->except('show')->parameters(['post-categories' => 'postCategory']);
    Route::resource('posts', PostController::class)->except('show');
    Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update']);
});

Route::get('/{domain}/{slug}', [SlugController::class, 'showByDomain'])
    ->where('domain', '[a-z0-9-]+')
    ->where('slug', '[a-z0-9-]+')
    ->name('content.show');

Route::get('/{post:slug}', [FrontendPostController::class, 'show'])->name('posts.show');
