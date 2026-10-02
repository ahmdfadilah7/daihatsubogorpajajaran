<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Server-side validation (FormRequest) rejects bad admin input with
 * field-level errors (design B.9 / AC #11). Named *RequestValidationTest*
 * so it is picked up by `php artisan test --filter=Request`.
 */
class RequestValidationTest extends TestCase
{
    use RefreshDatabase;

    private function validCar(array $overrides = []): array
    {
        return array_merge([
            'model' => 'Valid',
            'type' => '1.0',
            'category' => 'SUV',
            'year' => 2024,
            'price' => 100000000,
            'transmission' => 'CVT',
            'fuel' => 'Bensin',
            'seats' => 5,
            'accent1' => '#0a5fd1',
            'accent2' => '#123a8f',
            'img' => 'https://example.com/x.jpg',
        ], $overrides);
    }

    public function test_car_missing_model_is_rejected(): void
    {
        $user = User::factory()->create();
        $payload = $this->validCar();
        unset($payload['model']);

        $this->actingAs($user)->post(route('admin.cars.store'), $payload)
            ->assertSessionHasErrors('model');
    }

    public function test_car_non_hex_accent_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.cars.store'), $this->validCar(['accent1' => 'red']))
            ->assertSessionHasErrors('accent1');
    }

    public function test_car_negative_price_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.cars.store'), $this->validCar(['price' => -5]))
            ->assertSessionHasErrors('price');
    }

    public function test_car_requires_an_image_source_on_create(): void
    {
        $user = User::factory()->create();
        $payload = $this->validCar();
        unset($payload['img']); // no url and no upload

        $this->actingAs($user)->post(route('admin.cars.store'), $payload)
            ->assertSessionHasErrors('img');
    }

    public function test_testimonial_rating_outside_range_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.testimonials.store'), [
            'name' => 'A', 'city' => 'B', 'car' => 'Daihatsu', 'rating' => 9,
            'color' => '#0a5fd1', 'text' => 'bagus', 'img' => 'https://example.com/x.jpg',
        ])->assertSessionHasErrors('rating');
    }

    public function test_wheel_prize_weight_below_one_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.wheel-prizes.store'), [
            'label' => 'A', 'short' => 'B', 'color' => '#0a5fd1', 'weight' => 0, 'msg' => 'C',
        ])->assertSessionHasErrors('weight');
    }

    public function test_image_string_rejects_a_bare_filename(): void
    {
        $user = User::factory()->create();

        // Not http(s)://, img/, storage/, or upload/ -> rejected.
        $this->actingAs($user)->post(route('admin.cars.store'), $this->validCar(['img' => 'random.jpg']))
            ->assertSessionHasErrors('img');
    }

    public function test_icon_list_contains_all_seeded_icons(): void
    {
        $allowed = array_keys(config('icons.list'));
        $seeded = [
            // marquee seeds
            'fa-gas-pump', 'fa-shield-halved', 'fa-wrench', 'fa-hand-holding-dollar', 'fa-users', 'fa-award',
            // quiz seeds
            'fa-bullseye', 'fa-city', 'fa-people-roof', 'fa-mountain-sun', 'fa-truck-fast', 'fa-user',
            'fa-user-group', 'fa-people-group', 'fa-heart', 'fa-wand-magic-sparkles', 'fa-tag', 'fa-wallet',
            'fa-coins', 'fa-money-bill', 'fa-gem',
        ];

        $this->assertEmpty(array_diff($seeded, $allowed), 'All seeded icons must be present in config(icons.list).');
    }

    public function test_marquee_accepts_a_known_icon_and_rejects_an_unknown_one(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.marquee-items.store'), [
            'text' => 'Irit', 'icon' => 'fa-gas-pump', 'color' => '#0a5fd1', 'sort_order' => 0,
        ])->assertSessionDoesntHaveErrors('icon');

        $this->actingAs($user)->post(route('admin.marquee-items.store'), [
            'text' => 'Irit', 'icon' => 'fa-not-a-real-icon', 'color' => '#0a5fd1', 'sort_order' => 0,
        ])->assertSessionHasErrors('icon');
    }

    public function test_quiz_rejects_an_unknown_question_or_option_icon(): void
    {
        $user = User::factory()->create();

        // Unknown question icon.
        $this->actingAs($user)->post(route('admin.quiz-questions.store'), [
            'question' => 'Uji?', 'icon' => 'fa-not-a-real-icon',
            'options' => [['text' => 'A', 'icon' => 'fa-city', 'scores' => []]],
        ])->assertSessionHasErrors('icon');

        // Unknown option icon.
        $this->actingAs($user)->post(route('admin.quiz-questions.store'), [
            'question' => 'Uji?', 'icon' => 'fa-bullseye',
            'options' => [['text' => 'A', 'icon' => 'fa-not-a-real-icon', 'scores' => []]],
        ])->assertSessionHasErrors('options.0.icon');
    }
}
