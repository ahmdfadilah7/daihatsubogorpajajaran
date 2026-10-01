<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_settings_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('name="site_name"', false);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.settings.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_update_settings_and_upload_logo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $logo = UploadedFile::fake()->image('logo.png', 120, 120);

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'site_name' => 'Dealer Baru',
                'meta_title' => 'Dealer Baru | Resmi',
                'meta_description' => 'Deskripsi singkat dealer baru.',
                'meta_keywords' => 'dealer, baru',
                'logo_file' => $logo,
            ])
            ->assertRedirect(route('admin.settings.edit'));

        SiteSetting::flushCache();

        $this->assertSame('Dealer Baru', SiteSetting::get('site_name'));

        $storedLogo = SiteSetting::get('logo');
        $this->assertNotNull($storedLogo);
        $this->assertStringStartsWith('storage/uploads/', $storedLogo);
        Storage::disk('public')->assertExists(substr($storedLogo, strlen('storage/')));
    }

    public function test_meta_description_cannot_exceed_limit(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'site_name' => 'Dealer Baru',
                'meta_description' => str_repeat('a', 301),
            ])
            ->assertSessionHasErrors('meta_description');
    }
}
