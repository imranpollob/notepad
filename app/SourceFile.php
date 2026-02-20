<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SourceFile extends Model
{
    protected $fillable = [
        'source_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
    ];

    public function source()
    {
        return $this->belongsTo(Source::class);
    }
}
