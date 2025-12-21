<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    protected $fillable = [
        'number',
        'title',
        'synopsis',
        'video_url',
        'embed_url',
        'aired_at',
    ];
}
