<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_index_lists_users(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);
        User::factory()->create(['email' => 'rekan@example.com']);

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('admin@example.com');
        $response->assertSee('rekan@example.com');
    }

    public function test_user_can_be_created(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.users.store'), [
            'name' => 'Pengguna Baru',
            'email' => 'baru@example.com',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['email' => 'baru@example.com']);

        $created = User::where('email', 'baru@example.com')->first();
        $this->assertNotSame('secret-pass', $created->password);
        $this->assertTrue(Hash::check('secret-pass', $created->password));
    }

    public function test_create_requires_unique_email_and_confirmed_password(): void
    {
        $user = User::factory()->create(['email' => 'dipakai@example.com']);

        $this->actingAs($user)->post(route('admin.users.store'), [
            'name' => 'Duplikat',
            'email' => 'dipakai@example.com',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ])->assertSessionHasErrors('email');

        $this->actingAs($user)->post(route('admin.users.store'), [
            'name' => 'Mismatch',
            'email' => 'lain@example.com',
            'password' => 'secret-pass',
            'password_confirmation' => 'beda-pass',
        ])->assertSessionHasErrors('password');
    }

    public function test_user_can_be_updated_without_changing_password(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create(['name' => 'Lama', 'email' => 'lama@example.com']);
        $originalHash = $target->password;

        $this->actingAs($actor)->put(route('admin.users.update', $target), [
            'name' => 'Diperbarui',
            'email' => 'diperbarui@example.com',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('admin.users.index'));

        $target->refresh();
        $this->assertSame('Diperbarui', $target->name);
        $this->assertSame('diperbarui@example.com', $target->email);
        $this->assertSame($originalHash, $target->password);
    }

    public function test_user_update_changes_password_when_provided(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($actor)->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertTrue(Hash::check('password-baru', $target->fresh()->password));
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $actor = User::factory()->create();
        User::factory()->create();

        $this->actingAs($actor)->delete(route('admin.users.destroy', $actor));

        $this->assertNotNull(session('gagal'));
        $this->assertDatabaseHas('users', ['id' => $actor->id]);
    }

    public function test_cannot_delete_the_last_remaining_user(): void
    {
        $actor = User::factory()->create();
        $this->assertSame(1, User::count());

        // Deleting self is also the last user; the own-account guard fires first.
        $this->actingAs($actor)->delete(route('admin.users.destroy', $actor));

        $this->assertNotNull(session('gagal'));
        $this->assertDatabaseHas('users', ['id' => $actor->id]);
    }

    public function test_admin_can_delete_another_user(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($actor)->delete(route('admin.users.destroy', $target))
            ->assertRedirect(route('admin.users.index'));

        $this->assertNotNull(session('sukses'));
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }
}
