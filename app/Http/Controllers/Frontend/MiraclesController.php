<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Miracle;
use App\Models\Location;
use App\Repositories\MiraclesRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MiraclesController extends Controller
{
    public function index()
    {
        return Inertia::render('frontend/miracles/Index');
    }

    public function getJsonList(Request $request)
    {
        $q = $request->query('q');
        $locationId = $request->query('location_id');

        $miracles = MiraclesRepository::getFilteredList($q, $locationId);

        $locations = collect($miracles)
            ->flatMap(function ($m) {
                return $m['locations'] ?? [];
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
            'miracles' => $miracles,
            'locations' => $locations,
        ]);
    }

    public function show(string $slug)
    {
        $locale = app()->getLocale();

        $miracle = Miracle::with([
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

        $miracle->intro_image_url = $miracle->getFirstMediaUrl('intro_image');
        
        $miracle->texts->each(function ($text) {
            $text->image_url = $text->getFirstMediaUrl('images');
        });

        return Inertia::render('frontend/miracles/Show', [
            'miracle' => $miracle,
        ]);
    }
}
