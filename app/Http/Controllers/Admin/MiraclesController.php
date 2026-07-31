<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Miracle;
use App\Models\MiracleText;
use App\Models\MiracleTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class MiraclesController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/miracles/Index');
    }

    public function edit($miracleId)
    {
        if (is_numeric($miracleId)) {
            $miracle = Miracle::with(['translations', 'texts', 'locations'])->findOrFail($miracleId);
        } else {
            $miracle = new Miracle();
            $miracle->translations = [];
            $miracle->texts = [];
            $miracle->locations = [];
        }

        return Inertia::render('admin/miracles/Edit', [
            'miracle' => $miracle,
        ]);
    }

    public function save(Request $request, $miracleId = null)
    {
        $miraclePayload = [
            'happened_at' => $request->input('happened_at'),
            'published' => (int) (bool) $request->input('published'),
            'at_holy_mass' => (int) (bool) $request->input('at_holy_mass', false),
        ];

        $translationsData = $request->input('translations', []);
        $textsData = $request->input('texts', []);

        $miracle = DB::transaction(function () use ($miraclePayload, $translationsData, $textsData, $miracleId): Miracle {
            if (is_numeric($miracleId)) {
                $miracle = Miracle::findOrFail($miracleId);
                $miracle->update($miraclePayload);
            } else {
                $miracle = Miracle::create($miraclePayload);
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
                        'text' => $item['text'] ?? null,
                    ];

                    $textRecord = $existingTexts->firstWhere('pos', $position);

                    if ($textRecord) {
                        $textRecord->update($payload);
                    } else {
                        MiracleText::query()->create($payload);
                    }
                }

                $existingTexts->whereNotIn('pos', $positions)->each(function ($textRecord) {
                    $textRecord->delete();
                });
            }

            return $miracle;
        });

        return redirect()->route('admin.miracles.edit', $miracle->id)->with('success', __('admin.record_saved_successfully'));
    }
}
