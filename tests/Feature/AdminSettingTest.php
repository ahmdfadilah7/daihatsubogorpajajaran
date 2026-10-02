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

    public function test_updating_one_image_field_keeps_the_others(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        // Seed existing stored values for all three image fields.
        SiteSetting::updateOrCreate(['key' => 'logo'], ['value' => 'storage/uploads/logo-lama.png']);
        SiteSetting::updateOrCreate(['key' => 'favicon'], ['value' => 'storage/uploads/favicon-lama.png']);
        SiteSetting::updateOrCreate(['key' => 'og_image'], ['value' => 'storage/uploads/og-lama.png']);
        SiteSetting::flushCache();

        $newFavicon = UploadedFile::fake()->image('favicon-baru.png', 64, 64);

        // Update ONLY the favicon; leave logo and og_image untouched.
        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'site_name' => 'Dealer Baru',
                'meta_description' => 'Deskripsi singkat dealer baru.',
                'favicon_file' => $newFavicon,
            ])
            ->assertRedirect(route('admin.settings.edit'));

        SiteSetting::flushCache();

        // The favicon changed to a freshly stored path.
        $storedFavicon = SiteSetting::get('favicon');
        $this->assertNotSame('storage/uploads/favicon-lama.png', $storedFavicon);
        $this->assertStringStartsWith('storage/uploads/', $storedFavicon);
        Storage::disk('public')->assertExists(substr($storedFavicon, strlen('storage/')));

        // The other two image fields keep their previously stored values.
        $this->assertSame('storage/uploads/logo-lama.png', SiteSetting::get('logo'));
        $this->assertSame('storage/uploads/og-lama.png', SiteSetting::get('og_image'));
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

    public function test_admin_can_disable_a_feature_toggle(): void
    {
        $user = User::factory()->create();

        // Mirrors the form: an unchecked checkbox posts only the hidden '0'.
        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'site_name' => 'Dealer Baru',
                'feature_quiz' => '0',
                'feature_corner' => '1',
                'feature_wheel' => '1',
            ])
            ->assertRedirect(route('admin.settings.edit'));

        SiteSetting::flushCache();

        $this->assertSame('0', SiteSetting::get('feature_quiz'));
        $this->assertFalse(SiteSetting::enabled('feature_quiz'));
        $this->assertTrue(SiteSetting::enabled('feature_corner'));
        $this->assertTrue(SiteSetting::enabled('feature_wheel'));
    }

    public function test_feature_flag_defaults_to_enabled_when_missing(): void
    {
        // No row for feature_quiz yet; the convenience accessor defaults ON.
        $this->assertTrue(SiteSetting::enabled('feature_quiz'));
    }

    public function test_admin_can_update_credit_simulation_params(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'site_name' => 'Dealer Baru',
                'credit_interest_rate' => '8',
                'credit_default_dp' => '25',
                'credit_min_dp' => '10',
                'credit_max_dp' => '50',
                'credit_dp_step' => '5',
                'credit_default_tenor' => '4',
                'credit_min_tenor' => '1',
                'credit_max_tenor' => '6',
            ])
            ->assertRedirect(route('admin.settings.edit'));

        SiteSetting::flushCache();

        $this->assertSame('8', SiteSetting::get('credit_interest_rate'));
        $this->assertSame('25', SiteSetting::get('credit_default_dp'));
        $this->assertSame('6', SiteSetting::get('credit_max_tenor'));
    }

    public function test_credit_params_reject_min_dp_greater_than_max_dp(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'site_name' => 'Dealer Baru',
                'credit_min_dp' => '60',
                'credit_max_dp' => '50',
            ])
            ->assertSessionHasErrors('credit_min_dp');
    }

    public function test_credit_interest_rate_rejects_out_of_range(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'site_name' => 'Dealer Baru',
                'credit_interest_rate' => '150',
            ])
            ->assertSessionHasErrors('credit_interest_rate');
    }

    public function test_seeder_preserves_admin_edited_rate_but_adds_missing_keys(): void
    {
        // Admin already tuned the rate; a re-seed must NOT clobber it.
        SiteSetting::updateOrCreate(['key' => 'credit_interest_rate'], ['value' => '8']);
        SiteSetting::flushCache();

        $this->seed(\Database\Seeders\SiteSettingSeeder::class);
        SiteSetting::flushCache();

        $this->assertSame('8', SiteSetting::get('credit_interest_rate'));
        $this->assertSame('5', SiteSetting::get('credit_dp_step'));
    }

    public function test_home_emits_credit_bootstrap_and_car_select(): void
    {
        $this->seed(\Database\Seeders\SiteSettingSeeder::class);

        \App\Models\Car::create([
            'model' => 'Ayla',
            'type' => '1.0 M/T',
            'category' => 'city-car',
            'year' => 2024,
            'price' => 150000000,
            'transmission' => 'Manual',
            'fuel' => 'Bensin',
            'seats' => 5,
            'accent1' => '#000000',
            'accent2' => '#ffffff',
            'img' => 'cars/ayla.png',
            'sort_order' => 1,
        ]);

        $response = $this->get('/');
        $response->assertOk();
        // Credit bootstrap emitted inside the <script> as real JS, rate 0.04.
        $response->assertSee('App.CREDIT', false);
        $response->assertSee('rate: 0.04', false);
        // Car-driven price select with a car option carrying data-price.
        $response->assertSee('<select id="calcCar"', false);
        $response->assertSee('data-price="150000000"', false);
    }
}
