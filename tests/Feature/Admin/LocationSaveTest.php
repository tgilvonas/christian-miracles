<?php

namespace Tests\Feature\Admin;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_filters_locations_by_translated_name(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $location = Location::create(['name' => 'Paris']);
        $location->translations()->create([
            'lang' => 'en',
            'name' => 'Paris',
            'slug' => 'paris',
        ]);

        $otherLocation = Location::create(['name' => 'Rome']);
        $otherLocation->translations()->create([
            'lang' => 'en',
            'name' => 'Rome',
            'slug' => 'rome',
        ]);

        $response = $this->getJson(route('admin.locations.json_list', [
            'search_text' => 'Par',
            'paginate_by' => 10,
        ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $location->id);
    }

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
