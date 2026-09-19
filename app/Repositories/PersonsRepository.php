<?php

namespace App\Repositories;

use App\Models\Person;

class PersonsRepository
{
    public static function getFilteredList($search = null, $locationId = null, $socialStatusId = null)
    {
        $locale = app()->getLocale();

        $builder = Person::with(['translations', 'texts', 'locations.translations', 'socialStatuses.translations', 'media'])
            ->where('published', 1);

        if (!empty($locationId)) {
            $builder->whereHas('locations', function ($q) use ($locationId) {
                $q->where('id', $locationId);
            });
        }

        if (!empty($socialStatusId)) {
            $builder->whereHas('socialStatuses', function ($q) use ($socialStatusId) {
                $q->where('id', $socialStatusId);
            });
        }

        if (!empty($search)) {
            $s = trim($search);
            $builder->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhereHas('texts', function ($t2) use ($s) {
                        $t2->where('text', 'like', "%{$s}%");
                    });
            });
        }

        $persons = $builder->orderBy('name', 'asc')
            ->get()
            ->map(function ($p) use ($locale) {
                $p->intro_image_url = $p->getFirstMediaUrl('intro_image');

                $translation = $p->translations->firstWhere('lang', $locale) ?: $p->translations->first();

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'title' => $translation->name ?? null,
                    'slug' => $translation->slug ?? null,
                    'intro_image_url' => $p->intro_image_url,
                    'locations' => $p->locations->map(function ($loc) use ($locale) {
                        $lt = $loc->translations->firstWhere('lang', $locale) ?: $loc->translations->first();
                        return [
                            'id' => $loc->id,
                            'name' => $lt->name ?? null,
                            'slug' => $lt->slug ?? null,
                        ];
                    })->values(),
                    'social_statuses' => $p->socialStatuses->map(function ($socialStatus) use ($locale) {
                        $translation = $socialStatus->translations->firstWhere('lang', $locale) ?: $socialStatus->translations->first();
                        return [
                            'id' => $socialStatus->id,
                            'name' => $translation->name ?? $socialStatus->name,
                            'slug' => $translation->slug ?? null,
                        ];
                    })->values(),
                ];
            });

        return $persons;
    }
}
