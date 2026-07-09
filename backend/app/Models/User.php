<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Identity\Infrastructure\Models\Role;
use App\Modules\Identity\Infrastructure\Models\UserBusinessUnit;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
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
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')->withTimestamps();
    }

    public function businessUnitAssignments(): HasMany
    {
        return $this->hasMany(UserBusinessUnit::class);
    }

    public function businessUnits(): BelongsToMany
    {
        return $this->belongsToMany(BusinessUnit::class, 'user_business_units')
            ->withPivot(['role_id', 'role_key', 'is_active', 'permissions'])
            ->withTimestamps();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuperAdmin(): bool
    {
        return $this->roles->contains('key', 'super_admin');
    }

    public function permissionKeys(): array
    {
        return $this->roles
            ->flatMap(fn (Role $role) => $role->permissions->pluck('key'))
            ->merge(
                $this->businessUnitAssignments
                    ->filter(fn (UserBusinessUnit $assignment) => $assignment->is_active)
                    ->flatMap(fn (UserBusinessUnit $assignment) => $assignment->role?->permissions->pluck('key') ?? collect()),
            )
            ->unique()
            ->values()
            ->all();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->isSuperAdmin() || in_array($permission, $this->permissionKeys(), true);
    }
}
