<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SourceIngestion extends Model
{
    protected $fillable = [
        'source_id',
        'job_type',
        'status',
        'attempt',
        'started_at',
        'finished_at',
        'error_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function source()
    {
        return $this->belongsTo(Source::class);
    }
}
