<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentCategory extends Model
{
    public const ROLE_OPTIONS = [
        'pesantren' => 'Pesantren',
        'asesor' => 'Asesor',
        'admin' => 'Admin',
        'super_admin' => 'Super Admin',
    ];

    public const ASESOR_SCOPE_OPTIONS = [
        'all' => 'Ketua & Anggota',
        'ketua' => 'Ketua Asesor',
        'anggota' => 'Anggota Asesor',
    ];

    protected $fillable = [
        'name',
        'code',
        'description',
        'required_for_phase',
        'visible_to_roles',
        'asesor_scope',
        'template_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'visible_to_roles' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function isVisibleToRole(string $role, ?string $asesorScope = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $roles = $this->visible_to_roles ?: [];

        if ($roles === []) {
            return true;
        }

        if (! in_array($role, $roles, true)) {
            return false;
        }

        if ($role !== 'asesor') {
            return true;
        }

        $scope = $this->asesor_scope ?: 'all';

        return $scope === 'all' || $asesorScope === $scope;
    }

    public function getAsesorScopeLabel(): string
    {
        return self::ASESOR_SCOPE_OPTIONS[$this->asesor_scope ?: 'all'] ?? 'Ketua & Anggota';
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'category_id');
    }
}
