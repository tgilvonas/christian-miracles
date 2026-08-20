<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Miracle;
use App\Models\MiracleText;
use App\Models\MiracleTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MiraclesController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/miracles/Index');
    }

    public function getJsonList()
    {
        $query = Miracle::with('translations')->orderByDesc('id');

        if ($searchText = request('search_text')) {
            $query->whereHas('translations', function ($subQuery) use ($searchText) {
                $subQuery->where('name', 'like', '%' . $searchText . '%');
            });
        }

        $paginated = $query->paginate((int) request('paginate_by', 10));

        $paginated->getCollection()->transform(function (Miracle $miracle) {
            $record = $miracle->toArray();

            foreach ($miracle->translations as $translation) {
                $record['name_' . $translation->lang] = $translation->name;
                $record['slug_' . $translation->lang] = $translation->slug;
            }

            return $record;
        });

        return $paginated;
    }

    public function edit($miracleId)
    {
        if (is_numeric($miracleId)) {
            $miracle = Miracle::getMiracle($miracleId);
        } else {
            $miracle = new Miracle();
            $miracle->translations = [];
            $miracle->texts = [];
            $miracle->locations = [];
            $miracle->intro_image_url = '';
        }

        return Inertia::render('admin/miracles/Edit', [
            'miracle' => $miracle,
        ]);
    }

    public function save(Request $request, $miracleId = null)
    {
        $miraclePayload = [
            'happened_at' => $request->input('happened_at'),
            'year_to' => $request->input('year_to', null),
            'published' => (int) (bool) $request->input('published'),
            'at_holy_mass' => (int) (bool) $request->input('at_holy_mass', false),
        ];

        $translationsData = $request->input('translations', []);
        $textsData = $request->input('texts', []);

        $miracle = DB::transaction(function () use ($request, $miraclePayload, $translationsData, $textsData, $miracleId): Miracle {
            if (is_numeric($miracleId)) {
                $miracle = Miracle::findOrFail($miracleId);
                $miracle->update($miraclePayload);
            } else {
                $miracle = Miracle::create($miraclePayload);
            }

            $uploadedIntroImage = $request->file('intro_image');
            if ($uploadedIntroImage) {
                $miracle->clearMediaCollection('intro_image');
                $miracle->addMedia($uploadedIntroImage)->toMediaCollection('intro_image');
            }

            $incomingTranslationLocales = array_keys(is_array($translationsData) ? $translationsData : []);

            if (!empty($incomingTranslationLocales)) {
                $miracle->translations()->whereNotIn('lang', $incomingTranslationLocales)->delete();
            }

            foreach ($translationsData as $locale => $translationData) {
                $translationData = is_array($translationData) ? $translationData : [];

                $payload = array_merge($translationData, [
                    'lang' => $locale,
                    'miracle_id' => $miracle->id,
                ]);

                $translation = MiracleTranslation::query()
                    ->where('miracle_id', $miracle->id)
                    ->where('lang', $locale)
                    ->first();

                if ($translation) {
                    $translation->update($payload);
                } else {
                    MiracleTranslation::query()->create($payload);
                }
            }

            $incomingTextLocales = array_keys(is_array($textsData) ? $textsData : []);

            if (!empty($incomingTextLocales)) {
                $miracle->texts()->whereNotIn('lang', $incomingTextLocales)->delete();
            }

            foreach ($textsData as $locale => $items) {
                $items = is_array($items) ? array_values($items) : [];
                $existingTexts = MiracleText::query()
                    ->where('miracle_id', $miracle->id)
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
                        'miracle_id' => $miracle->id,
                        'title' => $item['title'] ?? null,
                        'anchor' => Str::slug($item['anchor'] ?? null),
                        'text' => $item['text'] ?? null,
                        'info_source' => $item['info_source'] ?? null,
                    ];

                    $textRecord = $existingTexts->firstWhere('pos', $position);

                    if ($textRecord) {
                        $textRecord->update($payload);
                    } else {
                        $textRecord = MiracleText::query()->create($payload);
                    }

                    $uploadedImage = $request->file("texts.{$locale}.{$index}.image");

                    if ($uploadedImage) {
                        $textRecord->clearMediaCollection('images');
                        $textRecord->addMedia($uploadedImage)->toMediaCollection('images');
                    }
                }

                $existingTexts->whereNotIn('pos', $positions)->each(function ($textRecord) {
                    $textRecord->delete();
                });
            }

            return $miracle;
        });

        return response()->json([
            'message' => __('admin.record_saved_successfully'),
            'miracle' => Miracle::getMiracle($miracle->id),
        ]);
    }

    public function delete($miracleId)
    {
        $miracle = Miracle::with(['translations', 'texts', 'locations'])->findOrFail($miracleId);

        foreach ($miracle->translations as $translation) {
            $translation->delete();
        }

        foreach ($miracle->texts as $text) {
            $text->delete();
        }

        $miracle->locations()->detach();
        $miracle->delete();

        return response()->json([
            'message' => __('admin.record_deleted_successfully'),
        ]);
    }
}
