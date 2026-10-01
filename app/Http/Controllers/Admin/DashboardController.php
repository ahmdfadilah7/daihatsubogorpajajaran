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

        return view('admin.dashboard', compact('counts'));
    }
}
