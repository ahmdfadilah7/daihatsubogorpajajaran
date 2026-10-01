<?php

namespace Tests\Feature;

use App\Models\CornerImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCornerImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_corner_image_is_stored_and_shown_on_home(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('pojok.jpg', 200, 200);

        $this->actingAs($user)->post(route('admin.corner-images.store'), [
            'alt' => 'Gambar Uji',
            'image' => $file,
        ])->assertRedirect(route('admin.corner-images.index'));

        $corner = CornerImage::firstWhere('alt', 'Gambar Uji');
        $this->assertNotNull($corner);

        // Stored on the public disk under uploads/, src carries storage/ prefix.
        $this->assertStringStartsWith('storage/uploads/', $corner->src);
        $relative = substr($corner->src, strlen('storage/'));
        Storage::disk('public')->assertExists($relative);

        // The widget's src is bootstrapped into window.App on the home page.
        // @json escapes "/" as "\/", so match on the slash-free filename.
        $filename = basename($corner->src);
        $body = $this->get('/')->getContent();
        $this->assertStringContainsString($filename, $body);
    }
}
