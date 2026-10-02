<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CategoryStyle;
use App\Models\CornerImage;
use App\Models\HeroSlide;
use App\Models\MarqueeItem;
use App\Models\QuizQuestion;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\WheelPrize;

class PublicSiteController extends Controller
{
    /**
     * Render the public landing page, bootstrapping every content group
     * from MySQL into the exact shapes the untouched JS modules expect.
     */
    public function home()
    {
        // CARS — ordered sort_order then id; accent as a 2-element array.
        // desc comes from the DB description column (empty string when blank,
        // car-detail.js then falls back to the generic category text).
        $cars = Car::orderBy('sort_order')->orderBy('id')->get()
            ->map(fn (Car $c) => [
                'id' => $c->id,
                'model' => $c->model,
                'type' => $c->type,
                'category' => $c->category,
                'year' => (int) $c->year,
                'price' => (int) $c->price,
                'transmission' => $c->transmission,
                'fuel' => $c->fuel,
                'seats' => (int) $c->seats,
                'badge' => $c->badge ?? '',
                'desc' => $c->description ?? '',
                'features' => $c->features ?? [],
                'accent' => [$c->accent1, $c->accent2],
                'img' => $c->img,
            ])
            ->values()
            ->toArray();

        // CAT_STYLE — keyed associative array (object), NEVER ->values().
        // category_styles has no sort_order column; order is irrelevant for a
        // keyed map (design B.11 exemption), so read it unordered.
        $catStyle = CategoryStyle::all()
            ->keyBy('category')
            ->map(fn (CategoryStyle $s) => [
                'bg' => $s->bg,
                'label' => $s->label,
            ])
            ->toArray();

        // QUIZ — nested; score is a model->points object map.
        $quiz = QuizQuestion::with(['options.scores'])
            ->orderBy('sort_order')->orderBy('id')->get()
            ->map(fn (QuizQuestion $q) => [
                'q' => $q->question,
                'icon' => $q->icon,
                'options' => $q->options->map(fn ($o) => [
                    't' => $o->text,
                    'icon' => $o->icon,
                    'score' => $o->scores->pluck('points', 'car_model'),
                ])->values(),
            ])
            ->values()
            ->toArray();

        // WHEEL_PRIZES — short keeps real \n for spin-wheel.js split('\n').
        $wheelPrizes = WheelPrize::orderBy('sort_order')->orderBy('id')->get()
            ->map(fn (WheelPrize $w) => [
                'label' => $w->label,
                'short' => $w->short,
                'color' => $w->color,
                'weight' => (int) $w->weight,
                'msg' => $w->msg,
            ])
            ->values()
            ->toArray();

        // CORNER_IMAGES — [src, alt].
        $cornerImages = CornerImage::orderBy('sort_order')->orderBy('id')->get()
            ->map(fn (CornerImage $ci) => [
                'src' => $ci->src,
                'alt' => $ci->alt,
            ])
            ->values()
            ->toArray();

        // HERO_SLIDES — DB column description -> JS key desc.
        $heroSlides = HeroSlide::orderBy('sort_order')->orderBy('id')->get()
            ->map(fn (HeroSlide $s) => [
                'name' => $s->name,
                'desc' => $s->description,
                'tag' => $s->tag,
                'price' => $s->price,
                'img' => $s->img,
            ])
            ->values()
            ->toArray();

        // TESTIMONIALS — [name, city, car, rating, color, img, text].
        $testimonials = Testimonial::orderBy('sort_order')->orderBy('id')->get()
            ->map(fn (Testimonial $t) => [
                'name' => $t->name,
                'city' => $t->city,
                'car' => $t->car,
                'rating' => (int) $t->rating,
                'color' => $t->color,
                'img' => $t->img,
                'text' => $t->text,
            ])
            ->values()
            ->toArray();

        // MARQUEE ITEMS — server-rendered pills for the "keunggulan berjalan"
        // strip; ordered and passed as an Eloquent collection (no JS bootstrap).
        $marqueeItems = MarqueeItem::orderBy('sort_order')->orderBy('id')->get();

        // SITE SETTINGS — resolved key-value map for <head> meta + branding.
        $settings = SiteSetting::allAsArray();

        // WHATSAPP — normalized digits-only international number (0->62) with a
        // safe fallback. Used by the Blade WA buttons and exposed to the public
        // JS modules as window.App.WA so every WA link uses the admin number.
        $waNumber = SiteSetting::whatsappNumber();

        return view('home', compact(
            'cars',
            'catStyle',
            'quiz',
            'wheelPrizes',
            'cornerImages',
            'heroSlides',
            'testimonials',
            'marqueeItems',
            'settings',
            'waNumber',
        ));
    }
}
