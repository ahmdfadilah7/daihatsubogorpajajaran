<?php

namespace Tests\Feature\Admin;

use App\Models\CornerImage;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Covers the deleteUploadedImage() cleanup wired into every upload-handling
 * admin controller: destroy removes the owned uploaded file, update-replace
 * removes the old uploaded file, and seeded img/ assets + remote URLs are
 * never deleted.
 *
 * All disk interaction uses Storage::fake('public') so no real files are
 * touched; see note in the step summary on what this proves vs. a real
 * browser upload-then-delete.
 */
class UploadCleanupTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A tiny test double exposing the protected trait method so we can assert
     * the guard directly, independent of any controller.
     */
    private function helper(): object
    {
        return new class
        {
            use \App\Http\Controllers\Admin\Concerns\ResolvesImageField;

            public function delete(?string $value): void
            {
                $this->deleteUploadedImage($value);
            }
        };
    }

    public function test_helper_deletes_only_our_uploaded_files(): void
    {
        Storage::fake('public');
        $helper = $this->helper();

        Storage::disk('public')->put('uploads/_mine.jpg', 'x');
        Storage::disk('public')->assertExists('uploads/_mine.jpg');

        $helper->delete('storage/uploads/_mine.jpg');

        Storage::disk('public')->assertMissing('uploads/_mine.jpg');
    }

    public function test_helper_ignores_urls_relative_assets_and_null(): void
    {
        Storage::fake('public');
        $helper = $this->helper();

        // Stage a seeded-style asset and a lookalike inside the fake disk.
        Storage::disk('public')->put('img/halo.jpeg', 'seed');

        // None of these are "storage/uploads/..." values, so nothing is deleted.
        $helper->delete('https://images.unsplash.com/x.jpg');
        $helper->delete('http://example.com/x.jpg');
        $helper->delete('img/halo.jpeg');
        $helper->delete('css/app.css');
        $helper->delete('js/app.js');
        $helper->delete(null);
        $helper->delete('');
        $helper->delete('   ');

        Storage::disk('public')->assertExists('img/halo.jpeg');
    }

    public function test_helper_is_a_noop_when_file_already_missing(): void
    {
        Storage::fake('public');
        $helper = $this->helper();

        // No exception even though the file does not exist.
        $helper->delete('storage/uploads/_gone.jpg');

        $this->assertTrue(true);
    }

    public function test_destroy_removes_owned_uploaded_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        Storage::disk('public')->put('uploads/_test_del.jpg', 'x');
        $corner = CornerImage::create([
            'src' => 'storage/uploads/_test_del.jpg',
            'alt' => 'Hapus Saya',
            'sort_order' => 0,
        ]);

        $this->actingAs($user)
            ->delete(route('admin.corner-images.destroy', $corner))
            ->assertRedirect(route('admin.corner-images.index'));

        $this->assertNull(CornerImage::find($corner->id));
        Storage::disk('public')->assertMissing('uploads/_test_del.jpg');
    }

    public function test_destroy_keeps_non_uploaded_asset(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        Storage::disk('public')->put('img/halo.jpeg', 'seed');
        $corner = CornerImage::create([
            'src' => 'img/halo.jpeg',
            'alt' => 'Aset Seed',
            'sort_order' => 0,
        ]);

        $this->actingAs($user)
            ->delete(route('admin.corner-images.destroy', $corner))
            ->assertRedirect(route('admin.corner-images.index'));

        $this->assertNull(CornerImage::find($corner->id));
        Storage::disk('public')->assertExists('img/halo.jpeg');
    }

    public function test_update_replace_deletes_old_uploaded_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        Storage::disk('public')->put('uploads/_test_old.jpg', 'old');
        $corner = CornerImage::create([
            'src' => 'storage/uploads/_test_old.jpg',
            'alt' => 'Ganti Gambar',
            'sort_order' => 0,
        ]);

        $new = UploadedFile::fake()->image('baru.jpg', 100, 100);

        $this->actingAs($user)
            ->put(route('admin.corner-images.update', $corner), [
                'alt' => 'Ganti Gambar',
                'image' => $new,
            ])
            ->assertRedirect(route('admin.corner-images.index'));

        $corner->refresh();

        // The new upload is stored and the old one is gone.
        $this->assertStringStartsWith('storage/uploads/', $corner->src);
        $this->assertNotSame('storage/uploads/_test_old.jpg', $corner->src);
        Storage::disk('public')->assertExists(substr($corner->src, strlen('storage/')));
        Storage::disk('public')->assertMissing('uploads/_test_old.jpg');
    }

    public function test_update_without_new_file_keeps_old_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        Storage::disk('public')->put('uploads/_keep.jpg', 'keep');
        $corner = CornerImage::create([
            'src' => 'storage/uploads/_keep.jpg',
            'alt' => 'Judul Lama',
            'sort_order' => 0,
        ]);

        // Edit only a text field, no new upload -> same src, file untouched.
        $this->actingAs($user)
            ->put(route('admin.corner-images.update', $corner), [
                'alt' => 'Judul Baru',
            ])
            ->assertRedirect(route('admin.corner-images.index'));

        $corner->refresh();
        $this->assertSame('storage/uploads/_keep.jpg', $corner->src);
        $this->assertSame('Judul Baru', $corner->alt);
        Storage::disk('public')->assertExists('uploads/_keep.jpg');
    }

    public function test_settings_update_replace_deletes_old_logo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        Storage::disk('public')->put('uploads/_logo_old.png', 'old');
        SiteSetting::updateOrCreate(['key' => 'logo'], ['value' => 'storage/uploads/_logo_old.png']);
        SiteSetting::flushCache();

        $newLogo = UploadedFile::fake()->image('logo-baru.png', 120, 120);

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'site_name' => 'Dealer',
                'logo_file' => $newLogo,
            ])
            ->assertRedirect(route('admin.settings.edit'));

        SiteSetting::flushCache();

        $stored = SiteSetting::get('logo');
        $this->assertNotSame('storage/uploads/_logo_old.png', $stored);
        $this->assertStringStartsWith('storage/uploads/', $stored);
        Storage::disk('public')->assertExists(substr($stored, strlen('storage/')));
        Storage::disk('public')->assertMissing('uploads/_logo_old.png');
    }
}
