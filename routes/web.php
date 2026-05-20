<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrontendController;
use Illuminate\Support\Facades\Route;

Route::controller(FrontendController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/about', 'about')->name('about');
    Route::get('/service', 'service')->name('service');
    Route::get('/feature', 'feature')->name('feature');
    Route::get('/team', 'team')->name('team');
    Route::get('/testimonial', 'testimonial')->name('testimonial');
    Route::get('/booking', 'booking')->name('booking');
    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'sendContact')->name('contact.send');
});

Route::controller(FrontendController::class)
    ->middleware('booking.auth')
    ->group(function () {
        Route::post('/booking', 'storeBooking')->name('booking.store');
    });

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'redirect'])->name('dashboard');
    Route::get('/user/dashboard', [DashboardController::class, 'user'])->name('user.dashboard');
    Route::get('/user/bookings', [DashboardController::class, 'bookings'])->name('user.bookings');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('admin')
        ->group(function () {
            Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
            Route::get('/users', [AdminController::class, 'users'])->name('users');
            Route::get('/bookings', [AdminController::class, 'bookings'])->name('bookings');
            Route::patch('/bookings/{booking}/status', [AdminController::class, 'updateBookingStatus'])->name('bookings.status');
            Route::get('/services', [AdminController::class, 'services'])->name('services');
            Route::get('/services/create', [AdminController::class, 'createService'])->name('services.create');
            Route::post('/services', [AdminController::class, 'storeService'])->name('services.store');
            Route::get('/services/{service}/edit', [AdminController::class, 'editService'])->name('services.edit');
            Route::put('/services/{service}', [AdminController::class, 'updateService'])->name('services.update');
            Route::delete('/services/{service}', [AdminController::class, 'destroyService'])->name('services.destroy');
            Route::get('/contacts', [AdminController::class, 'contacts'])->name('contacts');
        });
});

Route::redirect('/index.html', '/', 301);
Route::redirect('/about.html', '/about', 301);
Route::redirect('/service.html', '/service', 301);
Route::redirect('/feature.html', '/feature', 301);
Route::redirect('/team.html', '/team', 301);
Route::redirect('/testimonial.html', '/testimonial', 301);
Route::redirect('/quote', '/booking', 301);
Route::redirect('/quote.html', '/booking', 301);
Route::redirect('/contact.html', '/contact', 301);
