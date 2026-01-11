<?php

namespace App\Http\Controllers;

class LocaleController
{
    public function setLocale()
    {
        $locale = request('locale');
        if (array_key_exists($locale, config('app.website_locales'))) {
            session(['locale' => $locale]);
        }
        return response()->noContent();
    }

    public function getLocale()
    {
        return [
            'locale' => app()->getLocale(),
        ];
    }
}
