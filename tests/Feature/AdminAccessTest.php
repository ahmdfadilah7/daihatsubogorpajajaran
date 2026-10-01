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
