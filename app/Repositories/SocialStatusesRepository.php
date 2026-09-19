<?php

namespace App\Repositories;

use App\Models\SocialStatus;

class SocialStatusesRepository
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

        $queryObject = SocialStatus::query()->selectRaw('social_statuses.id, ' . implode(', ', $fieldsToSelect))->distinct();

        foreach ($tableAliases as $tableAlias) {
            $queryObject->leftJoin('social_statuses_translations AS ' . $tableAlias, function ($join) use ($tableAlias) {
                $join->on('social_statuses.id', '=', $tableAlias . '.social_status_id')
                    ->where($tableAlias . '.lang', '=', strtoupper(str_replace('translations_', '', $tableAlias)));
            });
        }

        if (strlen($searchText) > 2) {
            foreach ($locales as $locale) {
                $queryObject->orWhere('name_' . strtolower($locale), 'LIKE', '%' . $searchText . '%');
            }
        }

        $queryObject->orderBy('name_' . strtolower($currentLocale));

        if (is_numeric($paginateBy)) {
            return $queryObject->paginate($paginateBy);
        }

        return $queryObject->get();
    }
}
