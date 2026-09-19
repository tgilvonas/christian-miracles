<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialStatusTranslation extends Model
{
    protected $table = 'social_statuses_translations';

    protected $guarded = ['id'];

    public function socialStatus()
    {
        return $this->belongsTo(SocialStatus::class);
    }
}
