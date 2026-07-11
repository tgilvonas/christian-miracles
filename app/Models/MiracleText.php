<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MiracleText extends Model
{
    protected $table = 'miracles_texts';

    protected $guarded = ['id'];

    public function miracle()
    {
        return $this->belongsTo(Miracle::class, 'miracle_id', 'id');
    }
}
