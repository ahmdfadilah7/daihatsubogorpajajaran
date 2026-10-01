<?php

namespace Tests\Feature\Admin;

use App\Models\Car;
use App\Models\CornerImage;
use App\Models\HeroSlide;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\WheelPrize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Core bulk-delete behaviour for the generic (non-guarded) admin entities:
 * selected rows are removed, a success flash reports the count, unselected rows
 * survive, and validation rejects empty / non-existent ids.
 */
class BulkDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function carPayload(array $overrides = []): array
    {
        return array_merge([
            'model' => 'Bulkara',
            'type' => '1.0 Test',
            'category' => 'SUV',
            'year' => 2024,
            'price' => 199000000,
            'transmission' => 'CVT',
            'fuel' => 'Bensin',
            'seats' => 5,
            'accent1' => '#0a5fd1',
            'accent2' => '#123a8f',
            'img' => 'https://example.com/car.jpg',
        ], $overrides);
    }

    public function test_bulk_delete_removes_selected_cars_and_flashes_count(): void
    {
        $user = User::factory()->create();

        $a = Car::create($this->carPayload(['model' => 'A']));
        $b = Car::create($this->carPayload(['model' => 'B']));
        $c = Car::create($this->carPayload(['model' => 'C']));

        $this->actingAs($user)
            ->delete(route('admin.cars.bulk-destroy'), ['ids' => [$a->id, $b->id]])
            ->assertRedirect(route('admin.cars.index'))
            ->assertSessionHas('sukses', '2 data berhasil dihapus.');

        $this->assertDatabaseMissing('cars', ['id' => $a->id]);
        $this->assertDatabaseMissing('cars', ['id' => $b->id]);
        $this->assertDatabaseHas('cars', ['id' => $c->id]);
    }

    public function test_bulk_delete_rejects_empty_ids(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.cars.index'))
            ->delete(route('admin.cars.bulk-destroy'), ['ids' => []])
            ->assertSessionHasErrors('ids');
    }

    public function test_bulk_delete_rejects_nonexistent_id(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.cars.index'))
            ->delete(route('admin.cars.bulk-destroy'), ['ids' => [999999]])
            ->assertSessionHasErrors('ids.0');
    }

    public function test_bulk_delete_works_for_wheel_prizes(): void
    {
        $user = User::factory()->create();

        $a = WheelPrize::create(['label' => 'A', 'short' => 'a', 'color' => '#111', 'weight' => 1, 'msg' => 'm', 'sort_order' => 0]);
        $b = WheelPrize::create(['label' => 'B', 'short' => 'b', 'color' => '#222', 'weight' => 1, 'msg' => 'm', 'sort_order' => 1]);

        $this->actingAs($user)
            ->delete(route('admin.wheel-prizes.bulk-destroy'), ['ids' => [$a->id, $b->id]])
            ->assertRedirect(route('admin.wheel-prizes.index'))
            ->assertSessionHas('sukses', '2 data berhasil dihapus.');

        $this->assertDatabaseMissing('wheel_prizes', ['id' => $a->id]);
        $this->assertDatabaseMissing('wheel_prizes', ['id' => $b->id]);
    }

    public function test_bulk_delete_works_for_testimonials(): void
    {
        $user = User::factory()->create();

        $a = Testimonial::create(['name' => 'A', 'city' => 'X', 'car' => 'Ayla', 'rating' => 5, 'color' => '#111', 'img' => 'img/a.jpg', 'text' => 'ok', 'sort_order' => 0]);
        $b = Testimonial::create(['name' => 'B', 'city' => 'Y', 'car' => 'Rocky', 'rating' => 4, 'color' => '#222', 'img' => 'img/b.jpg', 'text' => 'ok', 'sort_order' => 1]);
        $c = Testimonial::create(['name' => 'C', 'city' => 'Z', 'car' => 'Terios', 'rating' => 3, 'color' => '#333', 'img' => 'img/c.jpg', 'text' => 'ok', 'sort_order' => 2]);

        $this->actingAs($user)
            ->delete(route('admin.testimonials.bulk-destroy'), ['ids' => [$a->id, $b->id]])
            ->assertSessionHas('sukses', '2 data berhasil dihapus.');

        $this->assertDatabaseMissing('testimonials', ['id' => $a->id]);
        $this->assertDatabaseHas('testimonials', ['id' => $c->id]);
    }

    public function test_bulk_delete_works_for_hero_slides(): void
    {
        $user = User::factory()->create();

        $a = HeroSlide::create(['name' => 'A', 'description' => 'd', 'tag' => 't', 'price' => 'Rp1', 'img' => 'img/a.jpg', 'sort_order' => 0]);
        $b = HeroSlide::create(['name' => 'B', 'description' => 'd', 'tag' => 't', 'price' => 'Rp2', 'img' => 'img/b.jpg', 'sort_order' => 1]);

        $this->actingAs($user)
            ->delete(route('admin.hero-slides.bulk-destroy'), ['ids' => [$a->id, $b->id]])
            ->assertSessionHas('sukses', '2 data berhasil dihapus.');

        $this->assertDatabaseMissing('hero_slides', ['id' => $a->id]);
        $this->assertDatabaseMissing('hero_slides', ['id' => $b->id]);
    }

    public function test_bulk_delete_works_for_corner_images(): void
    {
        $user = User::factory()->create();

        $a = CornerImage::create(['src' => 'img/a.jpg', 'alt' => 'A', 'sort_order' => 0]);
        $b = CornerImage::create(['src' => 'img/b.jpg', 'alt' => 'B', 'sort_order' => 1]);

        $this->actingAs($user)
            ->delete(route('admin.corner-images.bulk-destroy'), ['ids' => [$a->id, $b->id]])
            ->assertSessionHas('sukses', '2 data berhasil dihapus.');

        $this->assertDatabaseMissing('corner_images', ['id' => $a->id]);
        $this->assertDatabaseMissing('corner_images', ['id' => $b->id]);
    }
}
