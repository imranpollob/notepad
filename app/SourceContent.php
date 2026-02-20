<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SourceContent extends Model
{
    protected $fillable = [
        'source_id',
        'content_text',
        'content_html',
        'language',
        'word_count',
        'extracted_at',
    ];

    protected $casts = [
        'extracted_at' => 'datetime',
    ];

    public function source()
    {
        return $this->belongsTo(Source::class);
    }
}
