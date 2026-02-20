<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    protected $fillable = [
        'notebook_id',
        'created_by',
        'note_id',
        'source_type',
        'title',
        'origin_url',
        'status',
        'error_message',
        'checksum',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function notebook()
    {
        return $this->belongsTo(Notebook::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function note()
    {
        return $this->belongsTo(Notes::class, 'note_id');
    }

    public function files()
    {
        return $this->hasMany(SourceFile::class);
    }

    public function content()
    {
        return $this->hasOne(SourceContent::class);
    }

    public function ingestions()
    {
        return $this->hasMany(SourceIngestion::class);
    }

    public function chunks()
    {
        return $this->hasMany(SourceChunk::class);
    }
}
