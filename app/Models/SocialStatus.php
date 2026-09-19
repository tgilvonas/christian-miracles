<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialStatus extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function translations()
    {
        return $this->hasMany(SocialStatusTranslation::class);
    }

    public function persons()
    {
        return $this->belongsToMany(Person::class, 'persons_social_statuses', 'social_statuses_id', 'persons_id');
    }
}
