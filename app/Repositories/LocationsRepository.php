<?php

namespace App\Repositories;

use App\Models\Location;

class LocationsRepository
{
    public static function getTranslatedList($locales, $currentLocale, $searchText = '', $paginateBy = 10)
    {
        $fields = ['name', 'slug'];

        $fieldsToSelect = [];
        $tableAliases = [];

        foreach ($locales as $locale) {

            $tableAlias = 'translations_' . $locale;
            $tableAliases[] = $tableAlias;

            foreach ($fields as $field) {
                $fieldsToSelect[] = $tableAlias . '.' . $field . ' AS ' . $field . '_' . strtolower($locale);
            }
        }

        $queryObject = Location::query()->selectRaw('locations.id, ' . implode(', ', $fieldsToSelect))->distinct();
        
        foreach ($tableAliases as $tableAlias) {
            $queryObject->leftJoin('locations_translations AS ' . $tableAlias, function ($join) use ($tableAlias) {
                $localeCode = strtolower(str_replace('translations_', '', $tableAlias));

                $join->on('locations.id', '=', $tableAlias . '.location_id')
                    ->where($tableAlias . '.lang', '=', $localeCode);
            });
        }

        if (strlen($searchText) > 2) {
            $queryObject->where(function ($searchQuery) use ($locales, $searchText) {
                foreach ($locales as $locale) {
                    $tableAlias = 'translations_' . $locale;
                    $searchQuery->orWhere($tableAlias . '.name', 'LIKE', '%' . $searchText . '%');
                }
            });
        }

        $queryObject->orderBy('name_' . strtolower($currentLocale));

        if (is_numeric($paginateBy)) {
            return $queryObject->paginate($paginateBy);
        } else {
            return $queryObject->get();
        }
    }
}
