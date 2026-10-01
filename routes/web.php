<?php

use App\Http\Controllers\Admin\CarController;
use App\Http\Controllers\Admin\CategoryStyleController;
use App\Http\Controllers\Admin\CornerImageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\QuizQuestionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TestimonialController;
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
    Route::resource('cars', CarController::class)->except('show');
    Route::resource('category-styles', CategoryStyleController::class)->except('show');
    Route::resource('quiz-questions', QuizQuestionController::class)->except('show');
    Route::resource('wheel-prizes', WheelPrizeController::class)->except('show');
    Route::resource('corner-images', CornerImageController::class)->except('show');
    Route::resource('hero-slides', HeroSlideController::class)->except('show');
    Route::resource('testimonials', TestimonialController::class)->except('show');

    // Singleton website settings page (Pengaturan Website).
    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::match(['put', 'patch'], 'settings', [SettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
