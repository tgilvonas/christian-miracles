<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Miracle;
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
        $locale = app()->getLocale();

        $miracles = Miracle::with(['translations', 'texts', 'locations.translations', 'media'])
            ->where('published', 1)
            ->orderBy('happened_at', 'desc')
            ->get()
            ->map(function ($m) use ($locale) {
                $m->intro_image_url = $m->getFirstMediaUrl('intro_image');

                $translation = $m->translations->firstWhere('lang', $locale) ?: $m->translations->first();

                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'title' => $translation->name ?? null,
                    'slug' => $translation->slug ?? null,
                    'happened_at' => $m->happened_at,
                    'intro_image_url' => $m->intro_image_url,
                    'locations' => $m->locations->map(function ($loc) use ($locale) {
                        $lt = $loc->translations->firstWhere('lang', $locale) ?: $loc->translations->first();
                        return [
                            'id' => $loc->id,
                            'name' => $lt->name ?? null,
                            'slug' => $lt->slug ?? null,
                        ];
                    })->values(),
                ];
            });

        return response()->json($miracles);
    }

    public function show(string $slug)
    {
        $locale = app()->getLocale();

        $miracle = Miracle::with(['translations', 'texts.media', 'locations.translations', 'media'])
            ->whereHas('translations', function ($query) use ($slug, $locale) {
                $query->where('slug', $slug)->where('lang', $locale);
            })
            ->firstOrFail();

        $miracle->intro_image_url = $miracle->getFirstMediaUrl('intro_image');

        return Inertia::render('frontend/miracles/Show', [
            'miracle' => $miracle,
        ]);
    }
}
