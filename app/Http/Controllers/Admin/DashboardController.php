<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CategoryStyle;
use App\Models\CornerImage;
use App\Models\HeroSlide;
use App\Models\QuizQuestion;
use App\Models\Testimonial;
use App\Models\WheelPrize;

class DashboardController extends Controller
{
    public function index()
    {
        $counts = [
            'cars' => Car::count(),
            'categoryStyles' => CategoryStyle::count(),
            'quizQuestions' => QuizQuestion::count(),
            'wheelPrizes' => WheelPrize::count(),
            'cornerImages' => CornerImage::count(),
            'heroSlides' => HeroSlide::count(),
            'testimonials' => Testimonial::count(),
        ];

        // Chart data: number of cars per category (bar chart).
        $carsByCategory = Car::selectRaw('category, COUNT(*) as c')
            ->groupBy('category')
            ->orderBy('category')
            ->pluck('c', 'category');

        // Recent-items lists for the dashboard activity panels.
        $latestCars = Car::latest('id')->take(5)->get(['id', 'model', 'type', 'category', 'price', 'img']);
        $latestTestimonials = Testimonial::latest('id')->take(5)->get(['id', 'name', 'city', 'car', 'rating']);

        return view('admin.dashboard', compact(
            'counts',
            'carsByCategory',
            'latestCars',
            'latestTestimonials',
        ));
    }
}
