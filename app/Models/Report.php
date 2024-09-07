<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'reason',
        'reported_user',
        'user_reporting',
    ];

    public function posts()
    {
        return $this->belongsTo(Post::class);
    }
}
