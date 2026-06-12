<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'sso_groups' => 'array',
            'last_sso_login_at' => 'datetime',
        ];
    }

    public function pesantren()
    {
        return $this->hasOne(Pesantren::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasPermission(string $permissionKey): bool
    {
        $role = $this->relationLoaded('role')
            ? $this->role
            : $this->role()->with('permissions')->first();

        return $role?->permissions->contains('key', $permissionKey) ?? false;
    }

    public function asesor()
    {
        return $this->hasOne(Asesor::class);
    }
}
