<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PersonText extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = 'persons_texts';
    protected $guarded = ['id'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->singleFile();
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id', 'id');
    }
}
