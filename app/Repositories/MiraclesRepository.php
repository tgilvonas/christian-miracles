<?php

namespace App\Repositories;

use App\Models\Miracle;

class MiraclesRepository
{
    public static function getFilteredList()
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

        return $miracles;
    }
}
