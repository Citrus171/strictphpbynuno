<?php

declare(strict_types=1);

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UdemyAuthController;
use App\Http\Controllers\UdemyProjectController;
use App\Http\Controllers\UdemyTaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserEmailResetNotificationController;
use App\Http\Controllers\UserEmailVerificationController;
use App\Http\Controllers\UserEmailVerificationNotificationController;
use App\Http\Controllers\UserPasswordController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UserTwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('welcome'))->name('home');

// Storefront...
Route::get('cart', [CartController::class, 'index'])->name('cart.index');
Route::post('cart/items', [CartController::class, 'store'])->name('cart.items.store');
Route::patch('cart/items/{cartLineId}', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('cart/items/{cartLineId}', [CartController::class, 'destroy'])->name('cart.items.destroy');
Route::post('cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon.store');
Route::delete('cart/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.destroy');

Route::get('checkout/address', [CheckoutController::class, 'address'])->name('checkout.address');
Route::post('checkout/address', [CheckoutController::class, 'storeAddress'])->name('checkout.address.store');
Route::get('checkout/shipping', [CheckoutController::class, 'shipping'])->name('checkout.shipping');
Route::post('checkout/shipping', [CheckoutController::class, 'storeShipping'])->name('checkout.shipping.store');
Route::get('checkout/confirm', [CheckoutController::class, 'confirm'])->name('checkout.confirm');
Route::post('checkout/confirm', [CheckoutController::class, 'storeConfirm'])->name('checkout.confirm.store');
Route::get('checkout/complete/{order}', [CheckoutController::class, 'complete'])->name('checkout.complete');

Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::get('products/{slug}', [ProductController::class, 'show'])->name('products.show');

// Udemy Auth...
Route::post('udemy-auth/register', [UdemyAuthController::class, 'register'])->name('udemy-auth.register');
Route::post('udemy-auth/login', [UdemyAuthController::class, 'login'])->name('udemy-auth.login');
Route::middleware('auth:sanctum')->post('udemy-auth/logout', [UdemyAuthController::class, 'logout'])->name('udemy-auth.logout');

// Udemy Projects...
Route::get('udemy-projects', [UdemyProjectController::class, 'index'])->name('udemy-projects.index');
Route::get('udemy-projects/create', [UdemyProjectController::class, 'create'])->name('udemy-projects.create');
Route::post('udemy-projects', [UdemyProjectController::class, 'store'])->name('udemy-projects.store');
Route::get('udemy-projects/{udemyProject}', [UdemyProjectController::class, 'show'])->name('udemy-projects.show');
Route::get('udemy-projects/{udemyProject}/edit', [UdemyProjectController::class, 'edit'])->name('udemy-projects.edit');
Route::patch('udemy-projects/{udemyProject}', [UdemyProjectController::class, 'update'])->name('udemy-projects.update');
Route::delete('udemy-projects/{udemyProject}', [
    UdemyProjectController::class,
    'destroy',
])->name('udemy-projects.destroy');

// Udemy Tasks...
Route::get('udemy-tasks', [UdemyTaskController::class, 'index'])->name('udemy-tasks.index');
Route::get('udemy-tasks/create', [UdemyTaskController::class, 'create'])->name('udemy-tasks.create');
Route::post('udemy-tasks', [UdemyTaskController::class, 'store'])->name('udemy-tasks.store');
Route::get('udemy-tasks/{udemyTask}', [UdemyTaskController::class, 'show'])->name('udemy-tasks.show');
Route::get('udemy-tasks/{udemyTask}/edit', [UdemyTaskController::class, 'edit'])->name('udemy-tasks.edit');
Route::patch('udemy-tasks/{udemyTask}', [UdemyTaskController::class, 'update'])->name('udemy-tasks.update');
Route::delete('udemy-tasks/{udemyTask}', [UdemyTaskController::class, 'destroy'])->name('udemy-tasks.destroy');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', fn () => Inertia::render('dashboard'))->name('dashboard');
});

Route::middleware('auth')->group(function (): void {
    // User...
    Route::delete('user', [UserController::class, 'destroy'])->name('user.destroy');

    // User Profile...
    Route::redirect('settings', '/settings/profile');
    Route::get('settings/profile', [UserProfileController::class, 'edit'])->name('user-profile.edit');
    Route::patch('settings/profile', [UserProfileController::class, 'update'])->name('user-profile.update');

    // User Password...
    Route::get('settings/password', [UserPasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [UserPasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    // Appearance...
    Route::get('settings/appearance', fn () => Inertia::render('appearance/update'))->name('appearance.edit');

    // User Two-Factor Authentication...
    Route::get('settings/two-factor', [UserTwoFactorAuthenticationController::class, 'show'])
        ->name('two-factor.show');
});

Route::middleware('guest')->group(function (): void {
    // User...
    Route::get('register', [UserController::class, 'create'])
        ->name('register');
    Route::post('register', [UserController::class, 'store'])
        ->name('register.store');

    // User Password...
    Route::get('reset-password/{token}', [UserPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [UserPasswordController::class, 'store'])
        ->name('password.store');

    // User Email Reset Notification...
    Route::get('forgot-password', [UserEmailResetNotificationController::class, 'create'])
        ->name('password.request');
    Route::post('forgot-password', [UserEmailResetNotificationController::class, 'store'])
        ->name('password.email');

    // Session...
    Route::get('login', [SessionController::class, 'create'])
        ->name('login');
    Route::post('login', [SessionController::class, 'store'])
        ->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    // User Email Verification...
    Route::get('verify-email', [UserEmailVerificationNotificationController::class, 'create'])
        ->name('verification.notice');
    Route::post('email/verification-notification', [UserEmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // User Email Verification...
    Route::get('verify-email/{id}/{hash}', [UserEmailVerificationController::class, 'update'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // Session...
    Route::post('logout', [SessionController::class, 'destroy'])
        ->name('logout');
});
