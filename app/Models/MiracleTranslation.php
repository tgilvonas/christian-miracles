<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MiracleTranslation extends Model
{
    use SoftDeletes;

    protected $table = 'miracles_translations';

    protected $guarded = ['id'];

    public function miracle()
    {
        return $this->belongsTo(Miracle::class, 'miracle_id', 'id');
    }
}
