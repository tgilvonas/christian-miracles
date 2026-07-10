<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LocationRequest;
use App\Models\Location;
use App\Models\LocationTranslation;
use App\Repositories\LocationsRepository;
use Illuminate\Support\Facades\DB;
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

    public function create()
    {
        $location = new Location();

        $translations = [];
        foreach (config('app.website_locales') as $key => $locale) {
            $locationTranslation = new LocationTranslation();
            $locationTranslation->lang = $key;
            $translations[$key] = $locationTranslation;
        }

        return [
            'location' => $location,
            'translations' => $translations,
        ];
    }

    public function save(LocationRequest $request, $locationId = null)
    {
        $data = $request->validated();

        $locationData = $data['location'] ?? [];
        $translationsData = $data['translations'] ?? [];

        $location = DB::transaction(function () use ($locationData, $translationsData, $locationId) {
            if (is_numeric($locationId)) {
                $location = Location::findOrFail($locationId);
                $location->update($locationData);
            } else {
                $location = Location::create($locationData);
            }

            foreach ($translationsData as $locale => $translationData) {
                $translation = LocationTranslation::where('location_id', $location->id)
                    ->where('lang', $locale)
                    ->first();

                $payload = array_merge($translationData, [
                    'location_id' => $location->id,
                    'lang' => $locale,
                ]);

                if ($translation) {
                    $translation->update($payload);
                } else {
                    LocationTranslation::create($payload);
                }
            }

            return $location;
        });

        return response()->json([
            'message' => __('admin.location') . ' ' . __('admin.saved') . ' ' . __('admin.successfully'),
            'location' => $location,
        ]);
    }

    public function edit($locationId)
    {
        $location = Location::findOrFail($locationId);

        $translations = [];
        foreach (config('app.website_locales') as $key => $locale) {
            $translation = LocationTranslation::where('location_id', $location->id)
                ->where('lang', $key)
                ->first();

            if (!$translation) {
                $translation = new LocationTranslation();
                $translation->lang = $key;
            }

            $translations[$key] = $translation;
        }

        return [
            'location' => $location,
            'translations' => $translations,
        ];
    }

    public function delete($locationId)
    {
        $location = Location::findOrFail($locationId);
        
        foreach ($location->translations as $translation) {
            $translation->delete();
        }
        
        $location->delete();

        return response()->json([
            'message' => __('admin.location') . ' ' . __('admin.deleted') . ' ' . __('admin.successfully'),
        ]);
    }
}
