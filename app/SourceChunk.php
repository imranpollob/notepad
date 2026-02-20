<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SourceChunk extends Model
{
    protected $fillable = [
        'source_id',
        'chunk_index',
        'content',
        'token_count',
        'embedding',
        'embedding_model',
        'embedded_at',
    ];

    protected $casts = [
        'embedding' => 'array',
        'embedded_at' => 'datetime',
    ];

    public function source()
    {
        return $this->belongsTo(Source::class);
    }
}
