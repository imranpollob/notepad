<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notes extends Model
{
    protected $fillable = [
        'url',
        'data',
        'title',
        'password',
        'owner_id',
        'is_active'
    ];

    public function sources()
    {
        return $this->hasMany(Source::class, 'note_id');
    }
}
