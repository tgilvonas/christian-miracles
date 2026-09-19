<?php

namespace Tests\Feature\Admin;

use App\Models\Person;
use App\Models\PersonText;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_it_filters_persons_by_location_and_social_status(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $locationOne = \App\Models\Location::create(['name' => 'Rome']);
        $locationOne->translations()->create([
            'lang' => 'en',
            'name' => 'Rome',
            'slug' => 'rome',
        ]);

        $locationTwo = \App\Models\Location::create(['name' => 'Jerusalem']);
        $locationTwo->translations()->create([
            'lang' => 'en',
            'name' => 'Jerusalem',
            'slug' => 'jerusalem',
        ]);

        $socialStatusOne = \App\Models\SocialStatus::create(['name' => 'Martyr']);
        $socialStatusOne->translations()->create([
            'lang' => 'en',
            'name' => 'Martyr',
            'slug' => 'martyr',
        ]);

        $socialStatusTwo = \App\Models\SocialStatus::create(['name' => 'Bishop']);
        $socialStatusTwo->translations()->create([
            'lang' => 'en',
            'name' => 'Bishop',
            'slug' => 'bishop',
        ]);

        $matchingPerson = Person::create(['name' => 'Saint Rome', 'published' => 1]);
        $matchingPerson->locations()->sync([$locationOne->id]);
        $matchingPerson->socialStatuses()->sync([$socialStatusOne->id]);

        $otherPerson = Person::create(['name' => 'Saint Jerusalem', 'published' => 1]);
        $otherPerson->locations()->sync([$locationTwo->id]);
        $otherPerson->socialStatuses()->sync([$socialStatusTwo->id]);

        $response = $this->getJson(route('admin.persons.json_list', [
            'location_id' => $locationOne->id,
            'social_status_id' => $socialStatusOne->id,
        ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $matchingPerson->id);
    }

    public function test_it_searches_persons_by_text_content(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $matchingPerson = Person::create([
            'name' => 'Text Search Person',
            'published' => 1,
        ]);

        $matchingPerson->texts()->create([
            'lang' => 'en',
            'pos' => 1,
            'title' => 'Hidden search phrase',
            'text' => 'This paragraph contains the secret keyword for searching.',
            'info_source' => 'Source text',
        ]);

        $otherPerson = Person::create([
            'name' => 'Unrelated Person',
            'published' => 1,
        ]);

        $otherPerson->texts()->create([
            'lang' => 'en',
            'pos' => 1,
            'title' => 'Other topic',
            'text' => 'Nothing relevant here.',
            'info_source' => 'Other source',
        ]);

        $response = $this->getJson(route('admin.persons.json_list', [
            'search_text' => 'secret keyword',
        ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $matchingPerson->id);
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

    public function test_it_saves_translations_and_text_blocks_for_a_person(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson(route('admin.persons.save'), [
            'name' => 'Translated Person',
            'published' => true,
            'translations' => [
                'en' => [
                    'name' => 'Translated Person',
                    'slug' => 'translated-person',
                    'meta_description' => 'English bio summary',
                    'meta_keywords' => 'person, biography',
                    'biography' => 'English biography text',
                ],
            ],
            'texts' => [
                'en' => [
                    [
                        'lang' => 'en',
                        'pos' => 1,
                        'title' => 'Life story',
                        'text' => '<p>First block</p>',
                        'info_source' => 'Source one',
                    ],
                ],
            ],
        ]);

        $response->assertOk();

        $person = Person::query()->where('name', 'Translated Person')->firstOrFail();

        $this->assertDatabaseHas('persons_translations', [
            'person_id' => $person->id,
            'lang' => 'en',
            'name' => 'Translated Person',
            'slug' => 'translated-person',
        ]);

        $this->assertDatabaseHas('persons_texts', [
            'person_id' => $person->id,
            'lang' => 'en',
            'pos' => 1,
            'title' => 'Life story',
            'text' => '<p>First block</p>',
        ]);
    }

    public function test_it_assigns_multiple_social_statuses_to_a_person(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $socialStatusOne = \App\Models\SocialStatus::create(['name' => 'Noble']);
        $socialStatusOne->translations()->create([
            'lang' => 'en',
            'name' => 'Noble',
            'slug' => 'noble',
        ]);

        $socialStatusTwo = \App\Models\SocialStatus::create(['name' => 'Merchant']);
        $socialStatusTwo->translations()->create([
            'lang' => 'en',
            'name' => 'Merchant',
            'slug' => 'merchant',
        ]);

        $response = $this->postJson(route('admin.persons.save'), [
            'name' => 'Status Person',
            'published' => true,
            'social_statuses' => [$socialStatusOne->id, $socialStatusTwo->id],
            'translations' => [
                'en' => [
                    'name' => 'Status Person',
                    'slug' => 'status-person',
                    'meta_description' => 'Summary',
                    'meta_keywords' => 'person, status',
                    'biography' => 'Person biography',
                ],
            ],
            'texts' => [
                'en' => [
                    [
                        'lang' => 'en',
                        'pos' => 1,
                        'title' => 'Story',
                        'text' => '<p>Intro text</p>',
                    ],
                ],
            ],
        ]);

        $response->assertOk();

        $person = Person::query()->where('name', 'Status Person')->firstOrFail();

        $this->assertEqualsCanonicalizing(
            [$socialStatusOne->id, $socialStatusTwo->id],
            $person->socialStatuses()->pluck('social_statuses.id')->all()
        );
    }

    public function test_it_uploads_and_removes_person_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $createResponse = $this->postJson(route('admin.persons.save'), [
            'name' => 'Photo Person',
            'published' => true,
            'intro_image' => UploadedFile::fake()->image('person-intro.jpg'),
            'translations' => [
                'en' => [
                    'name' => 'Photo Person',
                    'slug' => 'photo-person',
                    'meta_description' => 'Summary',
                    'meta_keywords' => 'person, photo',
                    'biography' => 'Person biography',
                ],
            ],
            'texts' => [
                'en' => [
                    [
                        'lang' => 'en',
                        'pos' => 1,
                        'title' => 'Story',
                        'text' => '<p>Intro text</p>',
                        'image' => UploadedFile::fake()->image('block-image.jpg'),
                    ],
                ],
            ],
        ]);

        $createResponse->assertOk();

        $person = Person::query()->where('name', 'Photo Person')->firstOrFail();

        $this->assertTrue($person->getFirstMediaUrl('intro_image') !== '');
        $this->assertTrue($person->texts->first()->getFirstMediaUrl('images') !== '');

        $updateResponse = $this->postJson(route('admin.persons.save', ['personId' => $person->id]), [
            'name' => 'Photo Person',
            'published' => true,
            'remove_intro_image' => true,
            'translations' => [
                'en' => [
                    'name' => 'Photo Person',
                    'slug' => 'photo-person',
                    'meta_description' => 'Summary',
                    'meta_keywords' => 'person, photo',
                    'biography' => 'Person biography',
                ],
            ],
            'texts' => [
                'en' => [
                    [
                        'lang' => 'en',
                        'pos' => 1,
                        'title' => 'Story',
                        'text' => '<p>Intro text</p>',
                        'remove_image' => true,
                    ],
                ],
            ],
        ]);

        $updateResponse->assertOk();

        $person->refresh();
        $this->assertSame('', $person->getFirstMediaUrl('intro_image'));
        $this->assertSame('', $person->texts->first()->getFirstMediaUrl('images'));
    }
}
