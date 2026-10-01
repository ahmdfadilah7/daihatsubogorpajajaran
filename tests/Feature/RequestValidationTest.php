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
}
