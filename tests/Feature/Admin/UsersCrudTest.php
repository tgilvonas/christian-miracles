<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_manage_users(): void
    {
        $superadmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'super@example.com',
            'roles' => ['ROLE_SUPERADMIN'],
        ]);

        $this->actingAs($superadmin);

        $this->get(route('admin.users.index'))
            ->assertOk();

        $response = $this->postJson(route('admin.users.save'), [
            'name' => 'Editor One',
            'email' => 'editor-one@example.com',
            'password' => 'password123',
            'roles' => ['ROLE_EDITOR'],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'email' => 'editor-one@example.com',
        ]);

        $user = User::where('email', 'editor-one@example.com')->firstOrFail();

        $this->getJson(route('admin.users.edit', ['userId' => $user->id]))
            ->assertOk();

        $this->postJson(route('admin.users.save', ['userId' => $user->id]), [
            'name' => 'Editor One Updated',
            'email' => 'editor-one-updated@example.com',
            'password' => '',
            'roles' => ['ROLE_SUPERADMIN'],
        ])->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'editor-one-updated@example.com',
        ]);

        $this->deleteJson(route('admin.users.delete', ['userId' => $user->id]))
            ->assertOk();

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_editor_cannot_access_users_index(): void
    {
        $editor = User::factory()->create([
            'name' => 'Editor',
            'email' => 'editor@example.com',
            'roles' => ['ROLE_EDITOR'],
        ]);

        $this->actingAs($editor);

        $this->get(route('admin.users.index'))
            ->assertForbidden();
    }
}
