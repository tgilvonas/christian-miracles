<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $guarded = ['id'];

    public function translations()
    {
        return $this->hasMany(LocationTranslation::class);
    }

    public function miracles()
    {
        return $this->belongsToMany(Miracle::class, 'miracles_locations', 'location_id', 'miracle_id');
    }
}
