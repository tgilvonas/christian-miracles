<?php

namespace Tests\Feature\Admin;

use App\Models\SocialStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialStatusSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_and_updates_translations_without_duplicates(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $createPayload = [
            'social_status' => [],
            'translations' => [
                'en' => [
                    'name' => 'Scholar',
                    'slug' => 'scholar',
                    'meta_description' => 'Scholar description',
                    'meta_keywords' => 'scholar, noble',
                ],
            ],
        ];

        $this->postJson(route('admin.social_statuses.save'), $createPayload)
            ->assertOk();

        $socialStatus = SocialStatus::query()->latest()->firstOrFail();

        $this->assertDatabaseHas('social_statuses_translations', [
            'social_status_id' => $socialStatus->id,
            'lang' => 'en',
            'name' => 'Scholar',
            'slug' => 'scholar',
        ]);

        $updatePayload = [
            'social_status' => [],
            'translations' => [
                'en' => [
                    'name' => 'Updated Scholar',
                    'slug' => 'updated-scholar',
                    'meta_description' => 'Updated scholar description',
                    'meta_keywords' => 'updated, scholar',
                ],
            ],
        ];

        $this->postJson(route('admin.social_statuses.save', ['socialStatusId' => $socialStatus->id]), $updatePayload)
            ->assertOk();

        $this->assertDatabaseCount('social_statuses_translations', 1);
        $this->assertDatabaseHas('social_statuses_translations', [
            'social_status_id' => $socialStatus->id,
            'lang' => 'en',
            'name' => 'Updated Scholar',
            'slug' => 'updated-scholar',
            'meta_description' => 'Updated scholar description',
            'meta_keywords' => 'updated, scholar',
        ]);
    }
}
