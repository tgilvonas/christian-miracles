<?php

namespace Tests\Feature\Admin;

use App\Models\Miracle;
use App\Models\MiracleTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiracleCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_a_paginated_miracle_list_with_translated_fields(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $miracle = Miracle::create([
            'happened_at' => '2025-03-12',
            'published' => 1,
        ]);

        MiracleTranslation::create([
            'miracle_id' => $miracle->id,
            'lang' => 'en',
            'name' => 'List Miracle',
            'slug' => 'list-miracle',
        ]);

        $response = $this->getJson(route('admin.miracles.json_list'));

        $response->assertOk();
        $response->assertJsonPath('data.0.id', $miracle->id);
        $response->assertJsonPath('data.0.name_en', 'List Miracle');
        $response->assertJsonPath('data.0.slug_en', 'list-miracle');
    }

    public function test_it_can_delete_a_miracle(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $miracle = Miracle::create([
            'happened_at' => '2025-03-12',
            'published' => 1,
        ]);

        $response = $this->deleteJson(route('admin.miracles.delete', ['miracleId' => $miracle->id]));

        $response->assertOk();
        $this->assertSoftDeleted('miracles', ['id' => $miracle->id]);
    }
}
