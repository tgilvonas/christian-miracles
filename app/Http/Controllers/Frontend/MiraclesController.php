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
        return Inertia::render('Miracles');
    }

    public function getJsonList(Request $request)
    {
        $locale = app()->getLocale();

        $miracles = Miracle::with(['translations', 'texts', 'locations'])
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
                    'happened_at' => $m->happened_at,
                    'intro_image_url' => $m->intro_image_url,
                ];
            });

        return response()->json($miracles);
    }
}
