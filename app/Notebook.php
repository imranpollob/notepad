<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notebook extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'visibility',
        'share_token',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sources()
    {
        return $this->hasMany(Source::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }
}
