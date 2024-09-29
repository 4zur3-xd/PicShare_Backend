<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conversation extends Model
{
    use HasFactory;

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'user_conversations')
            ->withTimestamps(); // Add timestamps to pivot table
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_conversations', 'conversation_id', 'user_id');
    }
}
