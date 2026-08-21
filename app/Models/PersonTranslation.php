<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonTranslation extends Model
{
    use SoftDeletes;

    protected $table = 'persons_translations';
    protected $guarded = ['id'];

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id', 'id');
    }
}
