<?php

namespace Tests\Feature\Admin;

use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_a_paginated_person_list(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $person = Person::create([
            'name' => 'List Person',
            'beatified_at' => '2025-03-12',
            'canonized_at' => '2025-04-11',
            'published' => 1,
        ]);

        $response = $this->getJson(route('admin.persons.json_list'));

        $response->assertOk();
        $response->assertJsonPath('data.0.id', $person->id);
        $response->assertJsonPath('data.0.name', 'List Person');
    }

    public function test_it_can_create_and_delete_a_person(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson(route('admin.persons.save'), [
            'name' => 'Create Person',
            'beatified_at' => '2025-03-12',
            'canonized_at' => '2025-04-11',
            'published' => true,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('persons', [
            'name' => 'Create Person',
            'published' => 1,
        ]);

        $person = Person::query()->where('name', 'Create Person')->firstOrFail();

        $deleteResponse = $this->deleteJson(route('admin.persons.delete', ['personId' => $person->id]));

        $deleteResponse->assertOk();
        $this->assertSoftDeleted('persons', ['id' => $person->id]);
    }
}
