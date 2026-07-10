<?php

namespace Tests\Feature\Admin;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_and_updates_translations_without_duplicates(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $createPayload = [
            'location' => [],
            'translations' => [
                'en' => [
                    'name' => 'First title',
                    'slug' => 'first-title',
                    'meta_description' => 'First description',
                    'meta_keywords' => 'first, title',
                ],
            ],
        ];

        $this->postJson(route('admin.locations.save'), $createPayload)
            ->assertOk();

        $location = Location::query()->latest()->firstOrFail();

        $this->assertDatabaseHas('locations_translations', [
            'location_id' => $location->id,
            'lang' => 'en',
            'name' => 'First title',
            'slug' => 'first-title',
        ]);

        $updatePayload = [
            'location' => [],
            'translations' => [
                'en' => [
                    'name' => 'Updated title',
                    'slug' => 'updated-title',
                    'meta_description' => 'Updated description',
                    'meta_keywords' => 'updated, title',
                ],
            ],
        ];

        $this->postJson(route('admin.locations.save', ['locationId' => $location->id]), $updatePayload)
            ->assertOk();

        $this->assertDatabaseCount('locations_translations', 1);
        $this->assertDatabaseHas('locations_translations', [
            'location_id' => $location->id,
            'lang' => 'en',
            'name' => 'Updated title',
            'slug' => 'updated-title',
            'meta_description' => 'Updated description',
            'meta_keywords' => 'updated, title',
        ]);
    }
}
