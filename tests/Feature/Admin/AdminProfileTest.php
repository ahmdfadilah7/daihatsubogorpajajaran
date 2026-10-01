<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_profile_page_renders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('Informasi Profil');
        $response->assertSee('Ubah Kata Sandi');
    }

    public function test_admin_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch(route('admin.profile.update'), [
            'name' => 'Nama Baru',
            'email' => 'baru@example.com',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.profile.edit'));
        $this->assertNotNull(session('sukses'));

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('baru@example.com', $user->email);
    }

    public function test_admin_profile_email_must_be_unique(): void
    {
        $other = User::factory()->create(['email' => 'dipakai@example.com']);
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('admin.profile.update'), [
            'name' => $user->name,
            'email' => 'dipakai@example.com',
        ])->assertSessionHasErrors('email');
    }

    public function test_password_update_still_works_from_admin_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'password',
            'password' => 'kata-sandi-baru',
            'password_confirmation' => 'kata-sandi-baru',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('kata-sandi-baru', $user->fresh()->password));
    }
}
