<?php

use App\Http\Controllers\AdminCommissionController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AgentEarningsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MockPaymentController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TenantProfileController;



Route::get('/', [PageController::class, 'show'])
    ->defaults('page', 'home.index')->name('home');
Route::get('/index.html', [PageController::class, 'legacy'])
    ->defaults('target', 'home');

// Tenant pages
Route::middleware(['auth', 'role:tenant'])->group(function () {
    Route::get('/seeker/favorites', [FavoriteController::class, 'index'])
    ->name('seeker.favorites');

Route::post('/seeker/favorites/{property}', [FavoriteController::class, 'store'])
    ->name('seeker.favorites.store');

Route::delete('/seeker/favorites/{property}', [FavoriteController::class, 'destroy'])
    ->name('seeker.favorites.destroy');

Route::get('/seeker/properties/{property}/reviews', [ReviewController::class, 'index'])
    ->name('seeker.reviews.index');

    Route::post('/seeker/properties/{property}/reviews', [ReviewController::class, 'store'])
    ->name('seeker.reviews.store');

    Route::get('/seeker/reviews/{review}/edit', [ReviewController::class, 'edit'])
    ->name('seeker.reviews.edit');

    Route::put('/seeker/reviews/{review}', [ReviewController::class, 'update'])
    ->name('seeker.reviews.update');

Route::delete('/seeker/reviews/{review}', [ReviewController::class, 'destroy'])
    ->name('seeker.reviews.destroy');



    Route::get('/seeker/bookings', [PageController::class, 'show'])
        ->defaults('page', 'seeker.bookings')->name('seeker.bookings');
    Route::get('/seeker/bookings.html', [PageController::class, 'legacy'])
        ->defaults('target', 'seeker.bookings');

    Route::get('/seeker/chat', [PageController::class, 'show'])
        ->defaults('page', 'seeker.chat')->name('seeker.chat');
    Route::get('/seeker/chat.html', [PageController::class, 'legacy'])
        ->defaults('target', 'seeker.chat');

    // Route::get('/seeker/profile', [PageController::class, 'show'])
    //     ->defaults('page', 'seeker.profile')->name('seeker.profile');
Route::get('/seeker/profile', [TenantProfileController::class, 'show'])
    ->name('seeker.profile');

    Route::put('/seeker/profile', [TenantProfileController::class, 'update'])
    ->name('seeker.profile.update');



    Route::get('/seeker/profile.html', [PageController::class, 'legacy'])
        ->defaults('target', 'seeker.profile');

    Route::get('/seeker/property', [PageController::class, 'show'])
        ->defaults('page', 'seeker.property')->name('seeker.property');
    Route::get('/seeker/property.html', [PageController::class, 'legacy'])
        ->defaults('target', 'seeker.property');

    Route::get('/seeker/search', [PageController::class, 'show'])
        ->defaults('page', 'seeker.search')->name('seeker.search');
    Route::get('/seeker/search.html', [PageController::class, 'legacy'])
        ->defaults('target', 'seeker.search');

    // Mock payment is available only to the tenant who owns that payment.
    Route::post('/payments/{payment}/mock-confirm', [MockPaymentController::class, 'confirm'])
        ->name('payments.mock.confirm');
});

// Agent pages
Route::middleware(['auth', 'role:agent'])->group(function () {
    Route::get('/owner/account', [PageController::class, 'show'])
        ->defaults('page', 'owner.account')->name('owner.account');
    Route::get('/owner/account.html', [PageController::class, 'legacy'])
        ->defaults('target', 'owner.account');

    Route::get('/owner/chat', [PageController::class, 'show'])
        ->defaults('page', 'owner.chat')->name('owner.chat');
    Route::get('/owner/chat.html', [PageController::class, 'legacy'])
        ->defaults('target', 'owner.chat');

    Route::get('/owner/dashboard', [PageController::class, 'show'])
        ->defaults('page', 'owner.dashboard')->name('owner.dashboard');
    Route::get('/owner/dashboard.html', [PageController::class, 'legacy'])
        ->defaults('target', 'owner.dashboard');

    Route::get('/owner/properties', [PageController::class, 'show'])
        ->defaults('page', 'owner.properties')->name('owner.properties');
    Route::get('/owner/properties.html', [PageController::class, 'legacy'])
        ->defaults('target', 'owner.properties');

    Route::get('/owner/property-form', [PageController::class, 'show'])
        ->defaults('page', 'owner.property-form')->name('owner.property-form');
    Route::get('/owner/property-form.html', [PageController::class, 'legacy'])
        ->defaults('target', 'owner.property-form');

    Route::get('/owner/requests', [PageController::class, 'show'])
        ->defaults('page', 'owner.requests')->name('owner.requests');
    Route::get('/owner/requests.html', [PageController::class, 'legacy'])
        ->defaults('target', 'owner.requests');

    Route::get('/owner/earnings', [AgentEarningsController::class, 'index'])
        ->name('owner.earnings');
});

// Admin pages and commission management
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/chat', [PageController::class, 'show'])
        ->defaults('page', 'admin.chat')->name('admin.chat');
    Route::get('/admin/chat.html', [PageController::class, 'legacy'])
        ->defaults('target', 'admin.chat');

    Route::get('/admin/dashboard', [PageController::class, 'show'])
        ->defaults('page', 'admin.dashboard')->name('admin.dashboard');
    Route::get('/admin/dashboard.html', [PageController::class, 'legacy'])
        ->defaults('target', 'admin.dashboard');

    Route::get('/admin/places', [PageController::class, 'show'])
        ->defaults('page', 'admin.places')->name('admin.places');
    Route::get('/admin/places.html', [PageController::class, 'legacy'])
        ->defaults('target', 'admin.places');

    Route::get('/admin/users', [AdminUserController::class, 'index'])
        ->name('admin.users');
    Route::get('/admin/users.html', [AdminUserController::class, 'index']);
    Route::get('/admin/users/{user}', [AdminUserController::class, 'show'])
        ->whereNumber('user')->name('admin.users.show');

    Route::get('/admin/verifications', [PageController::class, 'show'])
        ->defaults('page', 'admin.verifications')->name('admin.verifications');
    Route::get('/admin/verifications.html', [PageController::class, 'legacy'])
        ->defaults('target', 'admin.verifications');

    Route::get('/admin/commissions', [AdminCommissionController::class, 'index'])
        ->name('admin.commissions');
    Route::post('/admin/commissions/rates', [AdminCommissionController::class, 'storeRate'])
        ->name('admin.commissions.rates.store');
    Route::put('/admin/commissions/rates/{rate}', [AdminCommissionController::class, 'updateRate'])
        ->whereNumber('rate')->name('admin.commissions.rates.update');
    Route::delete('/admin/commissions/rates/{rate}', [AdminCommissionController::class, 'destroyRate'])
        ->whereNumber('rate')->name('admin.commissions.rates.destroy');
});

// Existing non-auth pages remain as they were.
Route::get('/auth/forgot-password', [PageController::class, 'show'])
    ->defaults('page', 'auth.forgot-password')->name('auth.forgot-password');
Route::get('/auth/forgot-password.html', [PageController::class, 'legacy'])
    ->defaults('target', 'auth.forgot-password');
Route::middleware('guest')->group(function () {
    Route::get('/auth/login', [AuthController::class, 'showLogin'])
        ->name('auth.login');
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->name('auth.login.store');

    Route::get('/auth/register', [AuthController::class, 'showRegister'])
        ->name('auth.register');
    Route::post('/auth/register', [AuthController::class, 'register'])
        ->middleware('throttle:3,1')->name('auth.register.store');

});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')->name('logout');

Route::get('/auth/login.html', [PageController::class, 'legacy'])
    ->defaults('target', 'auth.login');
Route::get('/auth/onboarding', [PageController::class, 'show'])
    ->defaults('page', 'auth.onboarding')->name('auth.onboarding');
Route::get('/auth/onboarding.html', [PageController::class, 'legacy'])
    ->defaults('target', 'auth.onboarding');
Route::get('/auth/register.html', [PageController::class, 'legacy'])
    ->defaults('target', 'auth.register');
Route::get('/auth/reset-password', [PageController::class, 'show'])
    ->defaults('page', 'auth.reset-password')->name('auth.reset-password');
Route::get('/auth/reset-password.html', [PageController::class, 'legacy'])
    ->defaults('target', 'auth.reset-password');
Route::get('/auth/verify-email', [PageController::class, 'show'])
    ->defaults('page', 'auth.verify-email')->name('auth.verify-email');
Route::get('/auth/verify-email.html', [PageController::class, 'legacy'])
    ->defaults('target', 'auth.verify-email');

Route::post('/demo/notice', [PageController::class, 'demoNotice'])->name('demo.notice');

    
