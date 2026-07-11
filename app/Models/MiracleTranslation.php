<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MiracleTranslation extends Model
{
    protected $table = 'miracles_translations';

    protected $guarded = ['id'];

    public function miracle()
    {
        return $this->belongsTo(Miracle::class, 'miracle_id', 'id');
    }
}
