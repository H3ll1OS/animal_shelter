<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

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
        'api_token',
        'sms_opted_in_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date:Y-m-d',
            'sms_opted_in_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isActive(): bool
    {
        return $this->status === 'Active';
    }

    public function adoptions(): HasMany
    {
        return $this->hasMany(Adoption::class);
    }

    public function assignedPostAdoptionVisits(): HasMany
    {
        return $this->hasMany(PostAdoptionVisit::class, 'assigned_user_id');
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        [$module, $action] = array_pad(explode('.', $permission, 2), 2, null);

        return $module !== null && $action !== null && Permission::query()
            ->where('module', $module)
            ->where('action', $action)
            ->whereHas('roles', fn ($query) => $query->where('key', $this->role))
            ->exists();
    }

    public function issueApiToken(): string
    {
        $plainToken = Str::random(60);

        $this->forceFill([
            'api_token' => hash('sha256', $plainToken),
        ])->save();

        return $plainToken;
    }

    public function revokeApiToken(): void
    {
        $this->forceFill([
            'api_token' => null,
        ])->save();
    }
}
