<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class MiracleText extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = ['id'];

    protected $table = 'miracles_texts';

    public function miracle()
    {
        return $this->belongsTo(Miracle::class, 'miracle_id', 'id');
    }
}
