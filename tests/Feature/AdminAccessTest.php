<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_requires_authentication(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_reach_the_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertStatus(200);
    }

    public function test_dashboard_renders_stat_cards_charts_and_counts(): void
    {
        $user = User::factory()->create();

        // Seed a couple of cars so the counts are non-zero and verifiable.
        \App\Models\Car::create([
            'model' => 'Ayla', 'type' => 'Hatchback', 'category' => 'City Car',
            'year' => 2024, 'price' => 150000000, 'transmission' => 'MT', 'fuel' => 'Bensin',
            'seats' => 5, 'badge' => '', 'accent1' => '#fff', 'accent2' => '#000',
            'img' => 'ayla.jpg', 'sort_order' => 1,
        ]);
        \App\Models\Car::create([
            'model' => 'Terios', 'type' => 'SUV', 'category' => 'SUV',
            'year' => 2024, 'price' => 280000000, 'transmission' => 'AT', 'fuel' => 'Bensin',
            'seats' => 7, 'badge' => '', 'accent1' => '#fff', 'accent2' => '#000',
            'img' => 'terios.jpg', 'sort_order' => 2,
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);

        // Stat-card labels for all 7 content groups.
        foreach (['Mobil', 'Gaya Kategori', 'Pertanyaan Kuis', 'Hadiah Roda', 'Gambar Pojok', 'Slide Hero', 'Testimoni'] as $label) {
            $response->assertSee($label, false);
        }

        // Chart container elements present.
        $response->assertSee('id="contentDistributionChart"', false);
        $response->assertSee('id="carsByCategoryChart"', false);

        // Chart library is referenced via the Vite-built bundle (window.Chart init).
        $response->assertSee('window.Chart', false);
        // The compiled Vite JS bundle (which includes Chart.js) is referenced.
        $response->assertSee('build/assets/app-', false);

        // The 2 seeded cars count appears on the Mobil stat card.
        $response->assertSee('>2</p>', false);
    }

    public function test_all_seven_crud_index_pages_are_reachable(): void
    {
        $user = User::factory()->create();

        $sections = [
            'admin.cars.index',
            'admin.category-styles.index',
            'admin.quiz-questions.index',
            'admin.wheel-prizes.index',
            'admin.corner-images.index',
            'admin.hero-slides.index',
            'admin.testimonials.index',
        ];

        foreach ($sections as $route) {
            $this->actingAs($user)->get(route($route))->assertStatus(200);
        }
    }

    public function test_registration_route_is_disabled(): void
    {
        // POST /register returns 404 (route fully removed, not just hidden).
        $this->post('/register', [
            'name' => 'X',
            'email' => 'x@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->get('/register')->assertNotFound();
    }
}
