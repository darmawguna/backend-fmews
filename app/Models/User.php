<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    // Role constants
    private const ROLE_ADMINISTRATOR = 'administrator';
    private const ROLE_PETUGAS = 'petugas';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'created_by', // Track siapa yang membuat user ini
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
    public function getRole(): string
    {
        return $this->role;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
            'email' => $this->email,
            'is_active' => $this->is_active
        ];
    }

    // Helper methods untuk role checking
    public function isAdministrator()
    {
        return $this->role === self::ROLE_ADMINISTRATOR;
    }

    public function isPetugas()
    {
        return $this->role === self::ROLE_PETUGAS;
    }

    // Relationship: User yang dibuat oleh administrator
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }
}
