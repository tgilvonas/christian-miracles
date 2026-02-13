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
                $fieldsToSelect[] = $tableAlias . '.' . $field . ' AS ' . $field . '_' . $locale;
            }
        }

        $queryObject = Location::query()->selectRaw('locations.id, ' . implode($fieldsToSelect));

        foreach ($tableAliases as $tableAlias) {
            $queryObject->leftJoin('locations_translations AS ' . $tableAlias, 'locations.id', '=', $tableAlias . '.location_id');
        }

        if (strlen($searchText)>2) {
            foreach ($locales as $locale) {
                $queryObject->orWhere('name_' . $locale, 'LIKE', '%' . $searchText . '%');
            }
        }

        $queryObject->orderBy('name_' . $currentLocale);

        if (is_numeric($paginateBy)) {
            return $queryObject->paginate($paginateBy);
        } else {
            return $queryObject->get();
        }
    }
}
