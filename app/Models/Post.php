<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'url_image',
        'caption',
        'cmt_count',
        'like_count',
        'is_deleted',
        'type',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function sharedWithUsers()
    {
        return $this->belongsToMany(User::class, 'shared_post_withs', 'post_id', 'user_id');
    }
    public function userViews()
    {
        return $this->hasMany(UserView::class, 'post_id');
    }
    public function likes()
    {
        return $this->hasMany(UserLike::class, 'post_id');
    }
}
