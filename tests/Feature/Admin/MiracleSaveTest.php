<?php

namespace Tests\Feature\Admin;

use App\Models\Miracle;
use App\Models\MiracleText;
use App\Models\MiracleTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiracleSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_and_updates_translations_and_text_blocks(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $createPayload = [
            'happened_at' => '2025-03-12',
            'published' => true,
            'translations' => [
                'en' => [
                    'name' => 'First miracle',
                    'slug' => 'first-miracle',
                    'meta_description' => 'First description',
                    'meta_keywords' => 'first, miracle',
                    'description' => 'First narrative',
                ],
            ],
            'texts' => [
                'en' => [
                    [
                        'lang' => 'en',
                        'pos' => 1,
                        'text' => '<p>First block</p>',
                    ],
                ],
            ],
        ];

        $this->postJson(route('admin.miracles.save'), $createPayload)
            ->assertRedirect();

        $miracle = Miracle::query()->latest()->firstOrFail();

        $this->assertDatabaseHas('miracles', [
            'id' => $miracle->id,
            'happened_at' => '2025-03-12',
            'published' => 1,
        ]);

        $this->assertDatabaseHas('miracles_translations', [
            'miracle_id' => $miracle->id,
            'lang' => 'en',
            'name' => 'First miracle',
            'slug' => 'first-miracle',
            'description' => 'First narrative',
        ]);

        $this->assertDatabaseHas('miracles_texts', [
            'miracle_id' => $miracle->id,
            'lang' => 'en',
            'pos' => 1,
            'text' => '<p>First block</p>',
        ]);

        $updatePayload = [
            'happened_at' => '2025-04-20',
            'published' => false,
            'translations' => [
                'en' => [
                    'name' => 'Updated miracle',
                    'slug' => 'updated-miracle',
                    'meta_description' => 'Updated description',
                    'meta_keywords' => 'updated, miracle',
                    'description' => 'Updated narrative',
                ],
            ],
            'texts' => [
                'en' => [
                    [
                        'lang' => 'en',
                        'pos' => 1,
                        'text' => '<p>Updated block</p>',
                    ],
                    [
                        'lang' => 'en',
                        'pos' => 2,
                        'text' => '<p>Second block</p>',
                    ],
                ],
            ],
        ];

        $this->postJson(route('admin.miracles.save', ['miracleId' => $miracle->id]), $updatePayload)
            ->assertRedirect();

        $this->assertDatabaseCount('miracles_translations', 1);
        $this->assertDatabaseHas('miracles_translations', [
            'miracle_id' => $miracle->id,
            'lang' => 'en',
            'name' => 'Updated miracle',
            'slug' => 'updated-miracle',
            'description' => 'Updated narrative',
        ]);

        $this->assertDatabaseCount('miracles_texts', 2);
        $this->assertDatabaseHas('miracles_texts', [
            'miracle_id' => $miracle->id,
            'lang' => 'en',
            'pos' => 1,
            'text' => '<p>Updated block</p>',
        ]);
        $this->assertDatabaseHas('miracles_texts', [
            'miracle_id' => $miracle->id,
            'lang' => 'en',
            'pos' => 2,
            'text' => '<p>Second block</p>',
        ]);
    }
}
