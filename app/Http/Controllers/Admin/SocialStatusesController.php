<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SocialStatusRequest;
use App\Models\SocialStatus;
use App\Models\SocialStatusTranslation;
use App\Repositories\SocialStatusesRepository;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SocialStatusesController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/SocialStatuses');
    }

    public function getJsonList()
    {
        return SocialStatusesRepository::getTranslatedList(
            config('app.website_locales'),
            app()->getLocale(),
            request('search_text'),
            request('paginate_by')
        );
    }

    public function create()
    {
        $socialStatus = new SocialStatus();

        $translations = [];
        foreach (config('app.website_locales') as $key => $locale) {
            $socialStatusTranslation = new SocialStatusTranslation();
            $socialStatusTranslation->lang = $key;
            $translations[$key] = $socialStatusTranslation;
        }

        return [
            'social_status' => $socialStatus,
            'translations' => $translations,
        ];
    }

    public function save(SocialStatusRequest $request, $socialStatusId = null)
    {
        $data = $request->validated();

        $socialStatusData = $data['social_status'] ?? [];
        $translationsData = $data['translations'] ?? [];

        $socialStatus = DB::transaction(function () use ($socialStatusData, $translationsData, $socialStatusId) {
            if (is_numeric($socialStatusId)) {
                $socialStatus = SocialStatus::findOrFail($socialStatusId);
                $socialStatus->update($socialStatusData);
            } else {
                $socialStatus = SocialStatus::create($socialStatusData);
            }

            foreach ($translationsData as $locale => $translationData) {
                $translation = SocialStatusTranslation::where('social_status_id', $socialStatus->id)
                    ->where('lang', $locale)
                    ->first();

                $payload = array_merge($translationData, [
                    'social_status_id' => $socialStatus->id,
                    'lang' => $locale,
                ]);

                if ($translation) {
                    $translation->update($payload);
                } else {
                    SocialStatusTranslation::create($payload);
                }
            }

            return $socialStatus;
        });

        return response()->json([
            'message' => __('admin.record_saved_successfully'),
            'social_status' => $socialStatus,
        ]);
    }

    public function edit($socialStatusId)
    {
        $socialStatus = SocialStatus::findOrFail($socialStatusId);

        $translations = [];
        foreach (config('app.website_locales') as $key => $locale) {
            $translation = SocialStatusTranslation::where('social_status_id', $socialStatus->id)
                ->where('lang', $key)
                ->first();

            if (!$translation) {
                $translation = new SocialStatusTranslation();
                $translation->lang = $key;
            }

            $translations[$key] = $translation;
        }

        return [
            'social_status' => $socialStatus,
            'translations' => $translations,
        ];
    }

    public function delete($socialStatusId)
    {
        $socialStatus = SocialStatus::findOrFail($socialStatusId);

        foreach ($socialStatus->translations as $translation) {
            $translation->delete();
        }

        $socialStatus->delete();

        return response()->json([
            'message' => __('admin.record_deleted_successfully'),
        ]);
    }
}
