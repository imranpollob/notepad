<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConversationMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'role',
        'message',
        'metadata',
        'token_usage',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
