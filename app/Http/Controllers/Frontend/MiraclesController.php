<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Miracle;
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
        $miracles = MiraclesRepository::getFilteredList();

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
