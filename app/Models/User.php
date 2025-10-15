<?php

namespace App\Models;

use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'birth_date',
        'location',
        'avatar',
        'password',
        'role',
        'is_reliable',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'date_of_birth' => 'date',
            'is_reliable' => 'boolean'
        ];
    }
    // relations

    public function reviews() {
        return $this->hasMany(Review::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function likeDislikes() {
        return $this->hasMany(LikeDislike::class);
    }
    /**
     * Check if the user is an admin.
     */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isReliable(): bool
    {
        return $this->is_reliable;
    }

    /**
     * Envoyer la notification de vérification d'email
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
