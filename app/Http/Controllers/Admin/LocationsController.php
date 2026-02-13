<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\LocationsRepository;
use Inertia\Inertia;

class LocationsController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/Locations');
    }

    public function getJsonList()
    {
        return LocationsRepository::getTranslatedList(
            config('app.website_locales'),
            app()->getLocale(),
            request('search_text'),
            request('paginate_by')
        );
    }
}
