<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
      'user_id',
      'sender_id',
      'title',
      'content',
      'link_to',
      'notification_type',
      'is_seen',
      'is_read'  
    ];


    public function setLinkToAttribute($value)
    {
        $this->attributes['link_to'] = json_encode($value);
    }

    public function getLinkToAttribute($value)
    {
        return json_decode($value, true); 
    }

    public function user(): BelongsTo      
    {
        return $this->belongsTo(User::class);
    }
    public function sender(): BelongsTo      
    {
        return $this->belongsTo(User::class,'sender_id');
    }
}
