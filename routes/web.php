<?php

use App\Http\Controllers\Admin\CarController;
use App\Http\Controllers\Admin\CategoryStyleController;
use App\Http\Controllers\Admin\CornerImageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\MarqueeItemController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\QuizQuestionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WheelPrizeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home'])->name('home');

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin dashboard (design B.7) — auth-protected, Indonesian UI.
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Bulk-delete routes use a STATIC `bulk-destroy` segment registered BEFORE
    // each resource so they are not swallowed by the resource `{wildcard}`.
    Route::delete('cars/bulk-destroy', [CarController::class, 'bulkDestroy'])->name('cars.bulk-destroy');
    Route::resource('cars', CarController::class)->except('show');

    Route::delete('category-styles/bulk-destroy', [CategoryStyleController::class, 'bulkDestroy'])->name('category-styles.bulk-destroy');
    Route::resource('category-styles', CategoryStyleController::class)->except('show');

    Route::delete('quiz-questions/bulk-destroy', [QuizQuestionController::class, 'bulkDestroy'])->name('quiz-questions.bulk-destroy');
    Route::resource('quiz-questions', QuizQuestionController::class)->except('show');

    Route::delete('wheel-prizes/bulk-destroy', [WheelPrizeController::class, 'bulkDestroy'])->name('wheel-prizes.bulk-destroy');
    Route::resource('wheel-prizes', WheelPrizeController::class)->except('show');

    Route::delete('corner-images/bulk-destroy', [CornerImageController::class, 'bulkDestroy'])->name('corner-images.bulk-destroy');
    Route::resource('corner-images', CornerImageController::class)->except('show');

    Route::delete('marquee-items/bulk-destroy', [MarqueeItemController::class, 'bulkDestroy'])->name('marquee-items.bulk-destroy');
    Route::resource('marquee-items', MarqueeItemController::class)->except('show');

    Route::delete('hero-slides/bulk-destroy', [HeroSlideController::class, 'bulkDestroy'])->name('hero-slides.bulk-destroy');
    Route::resource('hero-slides', HeroSlideController::class)->except('show');

    Route::delete('testimonials/bulk-destroy', [TestimonialController::class, 'bulkDestroy'])->name('testimonials.bulk-destroy');
    Route::resource('testimonials', TestimonialController::class)->except('show');

    // User account management (Pengguna).
    Route::delete('users/bulk-destroy', [UserController::class, 'bulkDestroy'])->name('users.bulk-destroy');
    Route::resource('users', UserController::class)->except('show');

    // Current user's profile (Profil).
    Route::get('profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::match(['put', 'patch'], 'profile', [AdminProfileController::class, 'update'])->name('profile.update');

    // Singleton website settings page (Pengaturan Website).
    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::match(['put', 'patch'], 'settings', [SettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
