<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCarCrudTest extends TestCase
{
    use RefreshDatabase;

    private function carPayload(array $overrides = []): array
    {
        return array_merge([
            'model' => 'Testara',
            'type' => '1.0 Test',
            'category' => 'SUV',
            'year' => 2024,
            'price' => 199000000,
            'transmission' => 'CVT',
            'fuel' => 'Bensin',
            'seats' => 5,
            'badge' => 'Baru',
            'accent1' => '#0a5fd1',
            'accent2' => '#123a8f',
            'img' => 'https://example.com/car.jpg',
        ], $overrides);
    }

    public function test_created_car_appears_on_public_home(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.cars.store'), $this->carPayload())
            ->assertRedirect(route('admin.cars.index'));

        $this->assertDatabaseHas('cars', ['model' => 'Testara']);

        $body = $this->get('/')->getContent();
        $this->assertStringContainsString('Testara', $body);
    }

    public function test_car_can_be_updated_and_deleted(): void
    {
        $user = User::factory()->create();
        $car = Car::create($this->carPayload());

        $this->actingAs($user)
            ->put(route('admin.cars.update', $car), $this->carPayload(['model' => 'Diperbarui']))
            ->assertRedirect(route('admin.cars.index'));
        $this->assertDatabaseHas('cars', ['id' => $car->id, 'model' => 'Diperbarui']);

        $this->actingAs($user)
            ->delete(route('admin.cars.destroy', $car))
            ->assertRedirect(route('admin.cars.index'));
        $this->assertDatabaseMissing('cars', ['id' => $car->id]);
    }

    public function test_update_without_image_keeps_existing_image(): void
    {
        $user = User::factory()->create();
        $car = Car::create($this->carPayload(['img' => 'https://example.com/original.jpg']));

        $payload = $this->carPayload();
        unset($payload['img']); // no new image supplied on update

        $this->actingAs($user)->put(route('admin.cars.update', $car), $payload)
            ->assertRedirect(route('admin.cars.index'));

        $this->assertSame('https://example.com/original.jpg', $car->fresh()->img);
    }
}
