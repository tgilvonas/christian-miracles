<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Person extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = 'persons';
    protected $guarded = ['id'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('intro_image')->singleFile();
    }

    public function translations()
    {
        return $this->hasMany(PersonTranslation::class, 'person_id', 'id');
    }

    public function texts()
    {
        return $this->hasMany(PersonText::class, 'person_id', 'id');
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'persons_locations', 'person_id', 'location_id');
    }

    public static function getPerson(int $id)
    {
        $person = self::with(['translations', 'texts.media', 'locations'])->findOrFail($id);

        if ($person) {
            $person->intro_image_url = $person->getFirstMediaUrl('intro_image');
            $person->translations->each(function ($translation) {
                $translation->name = $translation->name ?? '';
            });
            $person->texts->each(function ($text) {
                $text->image_url = $text->getFirstMediaUrl('images');
            });
        }

        return $person;
    }
}
