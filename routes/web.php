<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DestinationController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\ContactSubmissionController;
use App\Http\Controllers\Admin\CommonController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TourCategoryController;
use App\Http\Controllers\Admin\TourController;
use App\Http\Controllers\Admin\TourImportController;
use App\Http\Controllers\Admin\TravelMomentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\AboutController;
use App\Http\Controllers\Frontend\BookingController as FrontendBookingController;
use App\Http\Controllers\Frontend\PageController as FrontendPageController;
use App\Http\Controllers\Admin\AboutPageController;
use App\Http\Controllers\Frontend\ContactController as FrontendContactController;
use App\Http\Controllers\Frontend\PostController as FrontendPostController;
use App\Http\Controllers\Frontend\ServiceController as FrontendServiceController;
use App\Http\Controllers\Frontend\SlugController;
use App\Http\Controllers\Frontend\TourController as FrontendTourController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/robots.txt', function () {
    return response(
        "User-agent: *\nDisallow:\n\nSitemap: ".url('/sitemap.xml')."\n",
        200,
        ['Content-Type' => 'text/plain; charset=UTF-8'],
    );
})->name('robots');
Route::get('/gioi-thieu', AboutController::class)->name('about');
Route::get('/tours', [FrontendTourController::class, 'index'])->name('tours.index');
Route::get('/tours/danh-muc/{category:slug}', [FrontendTourController::class, 'category'])->name('tours.category');
Route::get('/tours/{tour:slug}', [FrontendTourController::class, 'show'])->name('tours.show');
Route::get('/dich-vu', [FrontendServiceController::class, 'index'])->name('services.index');
Route::get('/dich-vu/danh-muc/{category}', [FrontendServiceController::class, 'category'])->name('services.category');
Route::get('/dich-vu/{service}', [FrontendServiceController::class, 'show'])->name('services.show');
Route::get('/dat-tour', [FrontendBookingController::class, 'create'])->name('booking.create');
Route::post('/dat-tour', [FrontendBookingController::class, 'store'])->middleware('throttle:10,1')->name('booking.store');
Route::get('/trang/{page:slug}', [FrontendPageController::class, 'show'])->name('pages.show');
Route::get('/cam-nang', [FrontendPostController::class, 'index'])->name('posts.index');
Route::get('/lien-he', [FrontendContactController::class, 'create'])->name('contact');
Route::post('/lien-he', [FrontendContactController::class, 'store'])->middleware('throttle:10,1')->name('contact.store');

Route::middleware('guest:admin')->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::post('/admin/logout', [AuthController::class, 'destroy'])->middleware('auth:admin')->name('admin.logout');

Route::prefix('admin')->name('admin.')->middleware(['auth:admin', 'admin', 'admin.permission'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('about', [AboutPageController::class, 'edit'])->name('about.edit');
    Route::put('about', [AboutPageController::class, 'update'])->name('about.update');
    Route::get('media', [MediaController::class, 'index'])->name('media.index');
    Route::get('media/list', [MediaController::class, 'list'])->name('media.list');
    Route::post('media/upload/temp', [MediaController::class, 'uploadTemp'])->name('media.upload.temp');
    Route::post('media/upload/editor', [MediaController::class, 'uploadEditor'])->name('media.upload.editor');
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::get('settings/website', [SettingController::class, 'website'])->name('settings.website');
    Route::put('settings/website', [SettingController::class, 'updateWebsite'])->name('settings.website.update');
    Route::get('settings/business', [SettingController::class, 'business'])->name('settings.business');
    Route::put('settings/business', [SettingController::class, 'updateBusiness'])->name('settings.business.update');
    Route::get('settings/media', [SettingController::class, 'media'])->name('settings.media');
    Route::put('settings/media', [SettingController::class, 'updateMedia'])->name('settings.media.update');
    Route::get('settings/seo', [SettingController::class, 'seo'])->name('settings.seo');
    Route::put('settings/seo', [SettingController::class, 'updateSeo'])->name('settings.seo.update');
    Route::get('settings/contact', [SettingController::class, 'contact'])->name('settings.contact');
    Route::put('settings/contact', [SettingController::class, 'updateContact'])->name('settings.contact.update');
    Route::get('settings/tour', [SettingController::class, 'tour'])->name('settings.tour');
    Route::put('settings/tour', [SettingController::class, 'updateTour'])->name('settings.tour.update');
    Route::resource('destinations', DestinationController::class)->except('show');
    Route::resource('tour-categories', TourCategoryController::class)->except('show')->parameters(['tour-categories' => 'tourCategory']);
    Route::resource('service-categories', ServiceCategoryController::class)->except('show')->parameters(['service-categories' => 'serviceCategory']);
    Route::resource('services', ServiceController::class)->except('show');
    Route::post('common/bulk-action', [CommonController::class, 'bulkAction'])->name('common.bulk-action');
    Route::post('common/reorder', [CommonController::class, 'reorder'])->name('common.reorder');
    Route::post('common/toggle', [CommonController::class, 'toggle'])->name('common.toggle');
    Route::get('tours/import', [TourImportController::class, 'create'])->name('tours.import.create');
    Route::post('tours/import/package', [TourImportController::class, 'importPackage'])->name('tours.import.package');
    Route::post('tours/import/schedules/prepare', [TourImportController::class, 'prepareSchedules'])->name('tours.import.schedules.prepare');
    Route::post('tours/import/schedules/confirm', [TourImportController::class, 'confirmSchedules'])->name('tours.import.schedules.confirm');
    Route::resource('tours', TourController::class)->except('show');
    Route::resource('travel-moments', TravelMomentController::class)->except('show')->parameters(['travel-moments' => 'travelMoment']);
    Route::resource('bookings', BookingController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::resource('pages', PageController::class)->except('show');
    Route::resource('post-categories', PostCategoryController::class)->except('show')->parameters(['post-categories' => 'postCategory']);
    Route::resource('posts', PostController::class)->except('show');
    Route::resource('sliders', SliderController::class)->except('show');
    Route::get('sliders/{slider}/items/{item}/edit', [SliderController::class, 'editItem'])->name('sliders.items.edit');
    Route::post('sliders/{slider}/items', [SliderController::class, 'storeItem'])->name('sliders.items.store');
    Route::put('sliders/{slider}/items/{item}', [SliderController::class, 'updateItem'])->name('sliders.items.update');
    Route::delete('sliders/{slider}/items/{item}', [SliderController::class, 'destroyItem'])->name('sliders.items.destroy');
    Route::resource('testimonials', TestimonialController::class)->except('show');
    Route::resource('promotions', PromotionController::class)->except('show');
    Route::resource('coupons', CouponController::class)->except('show');
    Route::resource('menus', MenuController::class)->except('show');
    Route::post('menus/{menu}/items', [MenuController::class, 'storeItem'])->name('menus.items.store');
    Route::put('menus/{menu}/items/{item}', [MenuController::class, 'updateItem'])->name('menus.items.update');
    Route::delete('menus/{menu}/items/{item}', [MenuController::class, 'destroyItem'])->name('menus.items.destroy');
    Route::get('contact-submissions', [ContactSubmissionController::class, 'index'])->name('contact-submissions.index');
    Route::get('contact-submissions/{contactSubmission}/edit', [ContactSubmissionController::class, 'edit'])->name('contact-submissions.edit');
    Route::put('contact-submissions/{contactSubmission}', [ContactSubmissionController::class, 'update'])->name('contact-submissions.update');
    Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::resource('roles', RoleController::class)->except('show');
});

Route::get('/{domain}/{slug}', [SlugController::class, 'showByDomain'])
    ->where('domain', '[a-z0-9-]+')
    ->where('slug', '[a-z0-9-]+')
    ->name('content.show');

Route::get('/{post:slug}', [FrontendPostController::class, 'show'])->name('posts.show');
