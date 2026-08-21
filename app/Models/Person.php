<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use SoftDeletes;

    protected $table = 'persons';
    protected $guarded = ['id'];

    public function translations()
    {
        return $this->hasMany(PersonTranslation::class, 'person_id', 'id');
    }

    public function texts()
    {
        return $this->hasMany(PersonText::class, 'person_id', 'id');
    }

    public static function getPerson(int $id)
    {
        $person = self::with(['translations', 'texts'])->findOrFail($id);

        if ($person) {
            $person->translations->each(function ($translation) {
                $translation->name = $translation->name ?? '';
            });
        }

        return $person;
    }
}
