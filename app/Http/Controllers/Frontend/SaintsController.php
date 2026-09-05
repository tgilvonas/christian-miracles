<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Repositories\PersonsRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaintsController extends Controller
{
    public function index()
    {
        return Inertia::render('frontend/saints/Index');
    }

    public function getJsonList(Request $request)
    {
        $q = $request->query('q');
        $locationId = $request->query('location_id');

        $persons = PersonsRepository::getFilteredList($q, $locationId);

        $locations = collect($persons)
            ->flatMap(function ($p) {
                return $p['locations'] ?? [];
            })
            ->unique('id')
            ->values()
            ->map(function ($loc) {
                return [
                    'id' => $loc['id'],
                    'name' => $loc['name'] ?? null,
                ];
            });

        return response()->json([
            'persons' => $persons,
            'locations' => $locations,
        ]);
    }

    public function show(string $slug)
    {
        $locale = app()->getLocale();

        $person = Person::with([
            'translations',
            'texts'  => function ($query) use ($locale) {
                $query->where('lang', $locale);
            },
            'texts.media',
            'locations.translations',
            'media'
        ])
            ->whereHas('translations', function ($query) use ($slug, $locale) {
                $query->where('slug', $slug)->where('lang', $locale);
            })
            ->firstOrFail();

        $person->intro_image_url = $person->getFirstMediaUrl('intro_image');

        $person->texts->each(function ($text) {
            $text->image_url = $text->getFirstMediaUrl('images');
        });

        return Inertia::render('frontend/saints/Show', [
            'person' => $person,
        ]);
    }
}
