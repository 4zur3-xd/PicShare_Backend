<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

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
        'fcm_code',
        'role',
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

    public function userLog():HasOne
    {
        return $this->hasOne(UserLog::class);
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
            "config" => [
                "language" => $this->language,
                "fcm_token" => $this->fcm_token,
            ],
        ];
    }
}
