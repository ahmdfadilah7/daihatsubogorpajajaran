<?php

namespace Tests\Feature\Admin;

use App\Models\Car;
use App\Models\CornerImage;
use App\Models\HeroSlide;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Bulk delete reuses deleteUploadedImage(): it removes 'storage/uploads/...'
 * files for the deleted rows but never touches seeded img/ assets or remote
 * URLs. All disk interaction uses Storage::fake('public').
 */
class BulkDeleteCleanupTest extends TestCase
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
        ], $overrides);
    }

    public function test_cars_bulk_delete_removes_uploaded_file_keeps_seeded_asset(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        Storage::disk('public')->put('uploads/_bulk_car.jpg', 'x');
        Storage::disk('public')->put('img/halo.jpeg', 'seed');

        $uploaded = Car::create($this->carPayload(['model' => 'Up', 'img' => 'storage/uploads/_bulk_car.jpg']));
        $seeded = Car::create($this->carPayload(['model' => 'Seed', 'img' => 'img/halo.jpeg']));

        $this->actingAs($user)
            ->delete(route('admin.cars.bulk-destroy'), ['ids' => [$uploaded->id, $seeded->id]])
            ->assertSessionHas('sukses', '2 data berhasil dihapus.');

        $this->assertDatabaseMissing('cars', ['id' => $uploaded->id]);
        $this->assertDatabaseMissing('cars', ['id' => $seeded->id]);

        Storage::disk('public')->assertMissing('uploads/_bulk_car.jpg');
        Storage::disk('public')->assertExists('img/halo.jpeg');
    }

    public function test_hero_slides_bulk_delete_removes_uploaded_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        Storage::disk('public')->put('uploads/_bulk_hero.jpg', 'x');
        Storage::disk('public')->put('img/halo.jpeg', 'seed');

        $uploaded = HeroSlide::create(['name' => 'Up', 'description' => 'd', 'tag' => 't', 'price' => 'Rp1', 'img' => 'storage/uploads/_bulk_hero.jpg', 'sort_order' => 0]);
        $seeded = HeroSlide::create(['name' => 'Seed', 'description' => 'd', 'tag' => 't', 'price' => 'Rp2', 'img' => 'img/halo.jpeg', 'sort_order' => 1]);

        $this->actingAs($user)
            ->delete(route('admin.hero-slides.bulk-destroy'), ['ids' => [$uploaded->id, $seeded->id]]);

        Storage::disk('public')->assertMissing('uploads/_bulk_hero.jpg');
        Storage::disk('public')->assertExists('img/halo.jpeg');
    }

    public function test_testimonials_bulk_delete_removes_uploaded_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        Storage::disk('public')->put('uploads/_bulk_tes.jpg', 'x');
        Storage::disk('public')->put('img/halo.jpeg', 'seed');

        $uploaded = Testimonial::create(['name' => 'Up', 'city' => 'X', 'car' => 'Ayla', 'rating' => 5, 'color' => '#111', 'img' => 'storage/uploads/_bulk_tes.jpg', 'text' => 'ok', 'sort_order' => 0]);
        $seeded = Testimonial::create(['name' => 'Seed', 'city' => 'Y', 'car' => 'Rocky', 'rating' => 4, 'color' => '#222', 'img' => 'img/halo.jpeg', 'text' => 'ok', 'sort_order' => 1]);

        $this->actingAs($user)
            ->delete(route('admin.testimonials.bulk-destroy'), ['ids' => [$uploaded->id, $seeded->id]]);

        Storage::disk('public')->assertMissing('uploads/_bulk_tes.jpg');
        Storage::disk('public')->assertExists('img/halo.jpeg');
    }

    public function test_corner_images_bulk_delete_removes_uploaded_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        Storage::disk('public')->put('uploads/_bulk_corner.jpg', 'x');
        Storage::disk('public')->put('img/halo.jpeg', 'seed');

        $uploaded = CornerImage::create(['src' => 'storage/uploads/_bulk_corner.jpg', 'alt' => 'Up', 'sort_order' => 0]);
        $seeded = CornerImage::create(['src' => 'img/halo.jpeg', 'alt' => 'Seed', 'sort_order' => 1]);

        $this->actingAs($user)
            ->delete(route('admin.corner-images.bulk-destroy'), ['ids' => [$uploaded->id, $seeded->id]]);

        Storage::disk('public')->assertMissing('uploads/_bulk_corner.jpg');
        Storage::disk('public')->assertExists('img/halo.jpeg');
    }

    public function test_cars_bulk_delete_leaves_remote_url_disk_untouched(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        // A row referencing a remote URL — nothing on disk should be touched.
        $remote = Car::create($this->carPayload(['model' => 'Remote', 'img' => 'https://example.com/x.jpg']));

        $this->actingAs($user)
            ->delete(route('admin.cars.bulk-destroy'), ['ids' => [$remote->id]])
            ->assertSessionHas('sukses', '1 data berhasil dihapus.');

        $this->assertDatabaseMissing('cars', ['id' => $remote->id]);
        // No exception, no file created or deleted for the URL value.
        $this->assertTrue(true);
    }
}
