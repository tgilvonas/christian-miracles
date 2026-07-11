<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Miracle extends Model
{
    protected $guarded = ['id'];

    public function translations()
    {
        return $this->hasMany(MiracleTranslation::class, 'miracle_id', 'id');
    }

    public function texts()
    {
        return $this->hasMany(MiracleText::class, 'miracle_id', 'id');
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'miracles_locations', 'miracle_id', 'location_id');
    }
}
