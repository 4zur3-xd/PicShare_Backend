<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'url_avatar',
        'user_code',
        'fcm_token',
        'role',
        'email_verified_at',
        'status',
        'language',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->user_code)) {
                do {
                    $user_code = strtoupper(Str::random(6));
                } while (User::where('user_code', $user_code)->exists());

                $user->user_code = $user_code;
            }
        });
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            "name" => $this->name,
            "email" => $this->email,
            "url_avatar" => $this->url_avatar,
            "role" => $this->role,
            "email_verified_at" => $this->email_verified_at,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "google_id" => $this->google_id,
            "user_code" => $this->user_code,
            "ban_status" => $this->status,
            "config" => [
                "language" => $this->language,
                "fcm_token" => $this->fcm_token,
            ],
        ];
    }
    public function userLog(): HasOne
    {
        return $this->hasOne(UserLog::class);
    }
    public function friends(): HasMany
    {
        return $this->hasMany(Friend::class);
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'user_conversations')->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
