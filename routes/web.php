<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;
use App\http\Controllers\FavoriteController;


Route::get('/', [PageController::class, 'show'])->defaults('page', 'home.index')->name('home');
Route::get('/index.html', [PageController::class, 'legacy'])->defaults('target', 'home');
Route::get('/seeker/bookings', [PageController::class, 'show'])->defaults('page', 'seeker.bookings')->name('seeker.bookings');
Route::get('/seeker/bookings.html', [PageController::class, 'legacy'])->defaults('target', 'seeker.bookings');
Route::get('/seeker/chat', [PageController::class, 'show'])->defaults('page', 'seeker.chat')->name('seeker.chat');
Route::get('/seeker/chat.html', [PageController::class, 'legacy'])->defaults('target', 'seeker.chat');
Route::get('/seeker/profile', [PageController::class, 'show'])->defaults('page', 'seeker.profile')->name('seeker.profile');
Route::get('/seeker/profile.html', [PageController::class, 'legacy'])->defaults('target', 'seeker.profile');
Route::get('/seeker/property', [PageController::class, 'show'])->defaults('page', 'seeker.property')->name('seeker.property');
Route::get('/seeker/property.html', [PageController::class, 'legacy'])->defaults('target', 'seeker.property');
Route::get('/seeker/search', [PageController::class, 'show'])->defaults('page', 'seeker.search')->name('seeker.search');
Route::get('/seeker/search.html', [PageController::class, 'legacy'])->defaults('target', 'seeker.search');
Route::get('/owner/account', [PageController::class, 'show'])->defaults('page', 'owner.account')->name('owner.account');
Route::get('/owner/account.html', [PageController::class, 'legacy'])->defaults('target', 'owner.account');
Route::get('/owner/chat', [PageController::class, 'show'])->defaults('page', 'owner.chat')->name('owner.chat');
Route::get('/owner/chat.html', [PageController::class, 'legacy'])->defaults('target', 'owner.chat');
Route::get('/owner/dashboard', [PageController::class, 'show'])->defaults('page', 'owner.dashboard')->name('owner.dashboard');
Route::get('/owner/dashboard.html', [PageController::class, 'legacy'])->defaults('target', 'owner.dashboard');
Route::get('/owner/properties', [PageController::class, 'show'])->defaults('page', 'owner.properties')->name('owner.properties');
Route::get('/owner/properties.html', [PageController::class, 'legacy'])->defaults('target', 'owner.properties');
Route::get('/owner/property-form', [PageController::class, 'show'])->defaults('page', 'owner.property-form')->name('owner.property-form');
Route::get('/owner/property-form.html', [PageController::class, 'legacy'])->defaults('target', 'owner.property-form');
Route::get('/owner/requests', [PageController::class, 'show'])->defaults('page', 'owner.requests')->name('owner.requests');
Route::get('/owner/requests.html', [PageController::class, 'legacy'])->defaults('target', 'owner.requests');
Route::get('/admin/chat', [PageController::class, 'show'])->defaults('page', 'admin.chat')->name('admin.chat');
Route::get('/admin/chat.html', [PageController::class, 'legacy'])->defaults('target', 'admin.chat');
Route::get('/admin/dashboard', [PageController::class, 'show'])->defaults('page', 'admin.dashboard')->name('admin.dashboard');
Route::get('/admin/dashboard.html', [PageController::class, 'legacy'])->defaults('target', 'admin.dashboard');
Route::get('/admin/places', [PageController::class, 'show'])->defaults('page', 'admin.places')->name('admin.places');
Route::get('/admin/places.html', [PageController::class, 'legacy'])->defaults('target', 'admin.places');
Route::get('/admin/users', [PageController::class, 'show'])->defaults('page', 'admin.users')->name('admin.users');
Route::get('/admin/users.html', [PageController::class, 'legacy'])->defaults('target', 'admin.users');
Route::get('/admin/verifications', [PageController::class, 'show'])->defaults('page', 'admin.verifications')->name('admin.verifications');
Route::get('/admin/verifications.html', [PageController::class, 'legacy'])->defaults('target', 'admin.verifications');
Route::get('/auth/forgot-password', [PageController::class, 'show'])->defaults('page', 'auth.forgot-password')->name('auth.forgot-password');
Route::get('/auth/forgot-password.html', [PageController::class, 'legacy'])->defaults('target', 'auth.forgot-password');
Route::get('/auth/login', [PageController::class, 'show'])->defaults('page', 'auth.login')->name('auth.login');
Route::get('/auth/login.html', [PageController::class, 'legacy'])->defaults('target', 'auth.login');
Route::get('/auth/onboarding', [PageController::class, 'show'])->defaults('page', 'auth.onboarding')->name('auth.onboarding');
Route::get('/auth/onboarding.html', [PageController::class, 'legacy'])->defaults('target', 'auth.onboarding');
Route::get('/auth/register', [PageController::class, 'show'])->defaults('page', 'auth.register')->name('auth.register');
Route::get('/auth/register.html', [PageController::class, 'legacy'])->defaults('target', 'auth.register');
Route::get('/auth/reset-password', [PageController::class, 'show'])->defaults('page', 'auth.reset-password')->name('auth.reset-password');
Route::get('/auth/reset-password.html', [PageController::class, 'legacy'])->defaults('target', 'auth.reset-password');
Route::get('/auth/verify-email', [PageController::class, 'show'])->defaults('page', 'auth.verify-email')->name('auth.verify-email');
Route::get('/auth/verify-email.html', [PageController::class, 'legacy'])->defaults('target', 'auth.verify-email');
Route::post('/demo/notice', [PageController::class, 'demoNotice'])->name('demo.notice');
Route::get('/seeker/favorites', [FavoriteController::class, 'index'])->middleware('auth')->name('seeker.favorites');
Route::post('/seeker/favorites/{property}', [FavoriteController::class, 'store'])
->middleware('auth')->name('seeker.favorites.store');
Route::delete('/seeker/favorites/{property}', [FavoriteController::class, 'destroy'])
    ->middleware('auth')
    ->name('seeker.favorites.destroy');

    /// i have to remove it when i merrger all the work
    if (app()->environment('local')) {
    Route::get('/preview/favorites', function () {
        return view('seeker.favorites', [
            'favorites' => collect(),
        ]);
    });
}