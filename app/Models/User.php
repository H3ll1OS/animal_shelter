<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    
    use HasFactory, Notifiable, SoftDeletes;

 
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'phone',
        'date_of_birth',
        'address',
        'profile_image',
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

    public function isDeveloper(): bool
    {
        return $this->role === 'developer';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->role === 'developer';
    }

    public function isActive(): bool
    {
        return $this->status === 'Active';
    }
}
