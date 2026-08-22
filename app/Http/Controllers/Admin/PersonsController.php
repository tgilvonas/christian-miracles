<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\PersonText;
use App\Models\PersonTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PersonsController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/persons/Index');
    }

    public function getJsonList()
    {
        $query = Person::with('translations')->orderByDesc('id');

        if ($searchText = request('search_text')) {
            $query->where(function ($searchQuery) use ($searchText) {
                $searchQuery->where('name', 'like', '%' . $searchText . '%');
                $searchQuery->orWhereHas('translations', function ($translationQuery) use ($searchText) {
                    $translationQuery->where('name', 'like', '%' . $searchText . '%');
                });
            });
        }

        $paginated = $query->paginate((int) request('paginate_by', 10));

        $paginated->getCollection()->transform(function (Person $person) {
            $record = $person->toArray();

            foreach ($person->translations as $translation) {
                $record['name_' . $translation->lang] = $translation->name;
                $record['slug_' . $translation->lang] = $translation->slug;
            }

            return $record;
        });

        return $paginated;
    }

    public function edit($personId)
    {
        if (is_numeric($personId)) {
            $person = Person::getPerson($personId);
        } else {
            $person = new Person();
            $person->translations = [];
            $person->texts = [];
            $person->locations = [];
        }

        return Inertia::render('admin/persons/Edit', [
            'person' => $person,
        ]);
    }

    public function save(Request $request, $personId = null)
    {
        $personPayload = [
            'name' => $request->input('name'),
            'beatified_at' => $request->input('beatified_at') ?: null,
            'canonized_at' => $request->input('canonized_at') ?: null,
            'published' => $request->boolean('published', false) ? 1 : 0,
        ];

        $translationsData = $request->input('translations', []);
        $textsData = $request->input('texts', []);

        $person = DB::transaction(function () use ($request, $personPayload, $translationsData, $textsData, $personId): Person {
            if (is_numeric($personId)) {
                $person = Person::findOrFail($personId);
                $person->update($personPayload);
            } else {
                $person = Person::create($personPayload);
            }

            $incomingTranslationLocales = array_keys(is_array($translationsData) ? $translationsData : []);

            if (!empty($incomingTranslationLocales)) {
                $person->translations()->whereNotIn('lang', $incomingTranslationLocales)->delete();
            }

            foreach ($translationsData as $locale => $translationData) {
                $translationData = is_array($translationData) ? $translationData : [];

                $payload = array_merge($translationData, [
                    'lang' => $locale,
                    'person_id' => $person->id,
                ]);

                $translation = PersonTranslation::query()
                    ->where('person_id', $person->id)
                    ->where('lang', $locale)
                    ->first();

                if ($translation) {
                    $translation->update($payload);
                } else {
                    PersonTranslation::query()->create($payload);
                }
            }

            $removeIntroImage = $request->boolean('remove_intro_image', false);
            $uploadedIntroImage = $request->file('intro_image');

            if ($removeIntroImage) {
                $person->clearMediaCollection('intro_image');
            }

            if ($uploadedIntroImage) {
                $person->clearMediaCollection('intro_image');
                $person->addMedia($uploadedIntroImage)->toMediaCollection('intro_image');
            }

            $incomingTextLocales = array_keys(is_array($textsData) ? $textsData : []);

            if (!empty($incomingTextLocales)) {
                $person->texts()->whereNotIn('lang', $incomingTextLocales)->delete();
            }

            foreach ($textsData as $locale => $items) {
                $items = is_array($items) ? array_values($items) : [];
                $existingTexts = PersonText::query()
                    ->where('person_id', $person->id)
                    ->where('lang', $locale)
                    ->get();

                $positions = [];

                foreach ($items as $index => $item) {
                    $item = is_array($item) ? $item : [];
                    $position = isset($item['pos']) ? (int) $item['pos'] : $index + 1;
                    $positions[] = $position;

                    $payload = [
                        'lang' => $locale,
                        'pos' => $position,
                        'person_id' => $person->id,
                        'title' => $item['title'] ?? null,
                        'text' => $item['text'] ?? null,
                        'info_source' => $item['info_source'] ?? null,
                    ];

                    $textRecord = $existingTexts->firstWhere('pos', $position);

                    if ($textRecord) {
                        $textRecord->update($payload);
                    } else {
                        $textRecord = PersonText::query()->create($payload);
                    }

                    $removeImage = $request->boolean("texts.{$locale}.{$index}.remove_image", false);
                    $uploadedImage = $request->file("texts.{$locale}.{$index}.image");

                    if ($removeImage) {
                        $textRecord->clearMediaCollection('images');
                    }

                    if ($uploadedImage) {
                        $textRecord->clearMediaCollection('images');
                        $textRecord->addMedia($uploadedImage)->toMediaCollection('images');
                    }
                }

                $existingTexts->whereNotIn('pos', $positions)->each(function ($textRecord) {
                    $textRecord->delete();
                });
            }

            $locationIds = array_values(array_filter(array_map('intval', (array) $request->input('locations', []))));
            $person->locations()->sync($locationIds);

            return $person;
        });

        $redirectRoute = route('admin.persons.edit', ['personId' => $person->id]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'redirect' => $redirectRoute,
                'success' => __('admin.record_saved_successfully'),
            ]);
        }

        return redirect()->route('admin.persons.edit', ['personId' => $person->id])
            ->with('success', __('admin.record_saved_successfully'));
    }

    public function delete($personId)
    {
        $person = Person::with(['translations', 'texts'])->findOrFail($personId);

        foreach ($person->translations as $translation) {
            $translation->delete();
        }

        foreach ($person->texts as $text) {
            $text->delete();
        }

        $person->delete();

        return response()->json([
            'message' => __('admin.record_deleted_successfully'),
        ]);
    }
}
