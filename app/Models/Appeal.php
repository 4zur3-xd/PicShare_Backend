<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Appeal extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'msg', 'status'];

    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'users');
    }
}
