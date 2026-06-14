<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Akreditasi;
use App\Models\DocumentCategory;
use App\Models\MasterEdpmButir;
use App\Models\MasterEdpmKomponen;
use App\Models\Permission;
use App\Models\Pesantren;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditTrailService;
use App\Services\PesantrenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MasterDataController extends Controller
{
    public function __construct(
        private AuditTrailService $auditTrail,
        private PesantrenService $pesantrenService,
    ) {}

    public function index()
    {
        $stats = [
            'komponen' => MasterEdpmKomponen::count(),
            'butir' => MasterEdpmButir::count(),
            'dokumen' => DocumentCategory::count(),
            'roles' => Role::count(),
            'users' => User::count(),
            'pesantren' => Pesantren::count(),
        ];

        return view('superadmin.master-data.index', compact('stats'));
    }

    public function edpm()
    {
        $komponens = MasterEdpmKomponen::withCount('butirs')->orderBy('id')->get();
        $butirs = MasterEdpmButir::with('komponen')->orderBy('komponen_id')->orderBy('id')->get();

        return view('superadmin.master-data.edpm.index', compact('komponens', 'butirs'));
    }

    public function storeKomponen(Request $request)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:100', 'unique:master_edpm_komponens,kode'],
            'nama' => ['required', 'string', 'max:255'],
        ]);

        $komponen = MasterEdpmKomponen::create([
            'kode' => $validated['kode'],
            'name' => $validated['nama'],
            'nama' => $validated['nama'],
        ]);

        $this->auditTrail->log('master_edpm_komponen_created', null, auth()->id(), [
            'komponen_id' => $komponen->id,
            'new' => $komponen->only(['kode', 'nama']),
        ]);

        return redirect()->route('superadmin.master-data.edpm.index')->with('success', 'Komponen EDPM berhasil ditambahkan.');
    }

    public function updateKomponen(Request $request, MasterEdpmKomponen $komponen)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:100', Rule::unique('master_edpm_komponens', 'kode')->ignore($komponen->id)],
            'nama' => ['required', 'string', 'max:255'],
        ]);

        $before = $komponen->only(['kode', 'nama']);

        $komponen->update([
            'kode' => $validated['kode'],
            'name' => $validated['nama'],
            'nama' => $validated['nama'],
        ]);

        $this->auditTrail->log('master_edpm_komponen_updated', null, auth()->id(), [
            'komponen_id' => $komponen->id,
            'old' => $before,
            'new' => $komponen->fresh()->only(['kode', 'nama']),
        ]);

        return redirect()->route('superadmin.master-data.edpm.index')->with('success', 'Komponen EDPM berhasil diperbarui.');
    }

    public function destroyKomponen(MasterEdpmKomponen $komponen)
    {
        $before = $komponen->only(['id', 'kode', 'nama']);

        $komponen->delete();

        $this->auditTrail->log('master_edpm_komponen_deleted', null, auth()->id(), [
            'old' => $before,
        ]);

        return redirect()->route('superadmin.master-data.edpm.index')->with('success', 'Komponen EDPM berhasil dihapus.');
    }

    public function storeButir(Request $request)
    {
        $validated = $request->validate([
            'komponen_id' => ['required', 'integer', 'exists:master_edpm_komponens,id'],
            'kode' => ['nullable', 'string', 'max:100'],
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $butir = MasterEdpmButir::create([
            'komponen_id' => $validated['komponen_id'],
            'kode' => $validated['kode'] ?? null,
            'name' => $validated['nama'],
            'nama' => $validated['nama'],
            'deskripsi' => $validated['deskripsi'] ?? null,
        ]);

        $this->auditTrail->log('master_edpm_butir_created', null, auth()->id(), [
            'butir_id' => $butir->id,
            'new' => $butir->only(['komponen_id', 'kode', 'nama', 'deskripsi']),
        ]);

        return redirect()->route('superadmin.master-data.edpm.index')->with('success', 'Butir EDPM berhasil ditambahkan.');
    }

    public function updateButir(Request $request, MasterEdpmButir $butir)
    {
        $validated = $request->validate([
            'komponen_id' => ['required', 'integer', 'exists:master_edpm_komponens,id'],
            'kode' => ['nullable', 'string', 'max:100'],
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $before = $butir->only(['komponen_id', 'kode', 'nama', 'deskripsi']);

        $butir->update([
            'komponen_id' => $validated['komponen_id'],
            'kode' => $validated['kode'] ?? null,
            'name' => $validated['nama'],
            'nama' => $validated['nama'],
            'deskripsi' => $validated['deskripsi'] ?? null,
        ]);

        $this->auditTrail->log('master_edpm_butir_updated', null, auth()->id(), [
            'butir_id' => $butir->id,
            'old' => $before,
            'new' => $butir->fresh()->only(['komponen_id', 'kode', 'nama', 'deskripsi']),
        ]);

        return redirect()->route('superadmin.master-data.edpm.index')->with('success', 'Butir EDPM berhasil diperbarui.');
    }

    public function destroyButir(MasterEdpmButir $butir)
    {
        $before = $butir->only(['id', 'komponen_id', 'kode', 'nama', 'deskripsi']);

        $butir->delete();

        $this->auditTrail->log('master_edpm_butir_deleted', null, auth()->id(), [
            'old' => $before,
        ]);

        return redirect()->route('superadmin.master-data.edpm.index')->with('success', 'Butir EDPM berhasil dihapus.');
    }

    public function documentCategories()
    {
        $categories = DocumentCategory::orderByDesc('is_active')->orderBy('name')->get();
        $roleOptions = DocumentCategory::ROLE_OPTIONS;
        $asesorScopeOptions = DocumentCategory::ASESOR_SCOPE_OPTIONS;
        $phaseOptions = $this->documentPhaseOptions();
        $presets = $this->documentRulePresets();
        $stats = [
            'total' => $categories->count(),
            'active' => $categories->where('is_active', true)->count(),
            'with_template' => $categories->filter(fn (DocumentCategory $category) => filled($category->template_path))->count(),
            'missing_rules' => $categories->filter(fn (DocumentCategory $category) => empty($category->visible_to_roles))->count(),
        ];

        return view('superadmin.master-data.document-categories.index', compact(
            'categories',
            'roleOptions',
            'asesorScopeOptions',
            'phaseOptions',
            'presets',
            'stats',
        ));
    }

    public function pesantren(Request $request)
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'lock' => $request->query('lock'),
            'readiness' => $request->query('readiness'),
        ];

        if (! in_array($filters['lock'], ['locked', 'unlocked'], true)) {
            $filters['lock'] = null;
        }

        if (! in_array($filters['readiness'], ['ready', 'incomplete'], true)) {
            $filters['readiness'] = null;
        }

        $query = Pesantren::query()
            ->with(['user', 'units'])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $keyword = '%'.$filters['q'].'%';

                $query->where(function ($query) use ($keyword) {
                    $query->where('nama_pesantren', 'like', $keyword)
                        ->orWhere('ns_pesantren', 'like', $keyword)
                        ->orWhereHas('user', function ($userQuery) use ($keyword) {
                            $userQuery->where('name', 'like', $keyword)
                                ->orWhere('email', 'like', $keyword);
                        });
                });
            })
            ->when($filters['lock'] === 'locked', fn ($query) => $query->where('is_locked', true))
            ->when($filters['lock'] === 'unlocked', fn ($query) => $query->where('is_locked', false))
            ->orderBy('nama_pesantren');

        $pesantrens = $query->get();
        $userIds = $pesantrens->pluck('user_id')->filter()->values();
        $activeAkreditasiCounts = Akreditasi::query()
            ->whereIn('user_id', $userIds)
            ->whereNotIn('status', Akreditasi::TERMINAL_STATUSES)
            ->select('user_id')
            ->selectRaw('count(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $allRows = $pesantrens
            ->map(function (Pesantren $pesantren) use ($activeAkreditasiCounts) {
                $completeness = $this->pesantrenService->checkDataCompleteness($pesantren->user_id);

                return [
                    'pesantren' => $pesantren,
                    'completeness' => $completeness,
                    'active_akreditasi_count' => (int) ($activeAkreditasiCounts[$pesantren->user_id] ?? 0),
                ];
            });

        $rows = $allRows
            ->when($filters['readiness'] === 'ready', fn ($rows) => $rows->filter(fn ($row) => $row['completeness']['assessmentReady']))
            ->when($filters['readiness'] === 'incomplete', fn ($rows) => $rows->filter(fn ($row) => ! $row['completeness']['assessmentReady']))
            ->values();

        $stats = [
            'total' => Pesantren::count(),
            'locked' => Pesantren::where('is_locked', true)->count(),
            'unlocked' => Pesantren::where('is_locked', false)->count(),
            'ready' => $allRows->where('completeness.assessmentReady', true)->count(),
            'incomplete' => $allRows->where('completeness.assessmentReady', false)->count(),
        ];
        $hasFilters = collect($filters)->contains(fn ($value) => filled($value));

        return view('superadmin.master-data.pesantren.index', compact('rows', 'filters', 'stats', 'hasFilters'));
    }

    public function togglePesantrenLock(Request $request, Pesantren $pesantren)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3'],
        ]);

        $oldStatus = (bool) $pesantren->is_locked;
        $pesantren->forceFill(['is_locked' => ! $oldStatus])->save();

        $this->auditTrail->log('pesantren_profile_lock_toggled', null, auth()->id(), [
            'pesantren_id' => $pesantren->id,
            'user_id' => $pesantren->user_id,
            'old' => ['is_locked' => $oldStatus],
            'new' => ['is_locked' => (bool) $pesantren->fresh()->is_locked],
        ], $validated['reason']);

        return redirect()
            ->route('superadmin.master-data.pesantren.index')
            ->with('success', $pesantren->fresh()->is_locked ? 'Data pesantren berhasil dikunci.' : 'Data pesantren berhasil dibuka.');
    }

    public function storeDocumentCategory(Request $request)
    {
        $validated = $this->validateDocumentCategory($request);
        $validated['code'] = $validated['code'] ?: Str::slug($validated['name'], '_');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['template_path'] = $this->storeDocumentCategoryTemplate($request);

        $category = DocumentCategory::create($validated);

        $this->auditTrail->log('document_category_created', null, auth()->id(), [
            'category_id' => $category->id,
            'new' => $category->only(['name', 'code', 'required_for_phase', 'visible_to_roles', 'asesor_scope', 'is_active', 'template_path']),
        ]);

        return redirect()->route('superadmin.master-data.document-categories.index')->with('success', 'Aturan kategori dokumen berhasil ditambahkan.');
    }

    public function updateDocumentCategory(Request $request, DocumentCategory $category)
    {
        $validated = $this->validateDocumentCategory($request, $category);
        $validated['code'] = $validated['code'] ?: Str::slug($validated['name'], '_');
        $validated['is_active'] = $request->boolean('is_active');

        if ($templatePath = $this->storeDocumentCategoryTemplate($request)) {
            $validated['template_path'] = $templatePath;
        }

        $before = $category->only(['name', 'code', 'required_for_phase', 'visible_to_roles', 'asesor_scope', 'is_active', 'template_path']);

        $category->update($validated);

        $this->auditTrail->log('document_category_updated', null, auth()->id(), [
            'category_id' => $category->id,
            'old' => $before,
            'new' => $category->fresh()->only(['name', 'code', 'required_for_phase', 'visible_to_roles', 'asesor_scope', 'is_active', 'template_path']),
        ]);

        return redirect()->route('superadmin.master-data.document-categories.index')->with('success', 'Aturan kategori dokumen berhasil diperbarui.');
    }

    public function toggleDocumentCategory(DocumentCategory $category)
    {
        $oldStatus = $category->is_active;

        $category->update(['is_active' => ! $category->is_active]);

        $this->auditTrail->log('document_category_toggled', null, auth()->id(), [
            'category_id' => $category->id,
            'old' => ['is_active' => $oldStatus],
            'new' => ['is_active' => $category->fresh()->is_active],
        ]);

        return redirect()->route('superadmin.master-data.document-categories.index')->with('success', 'Status kategori dokumen berhasil diubah.');
    }

    public function destroyDocumentCategory(DocumentCategory $category)
    {
        $before = $category->only(['id', 'name', 'code', 'required_for_phase', 'visible_to_roles', 'asesor_scope', 'is_active', 'template_path']);

        $category->delete();

        $this->auditTrail->log('document_category_deleted', null, auth()->id(), [
            'old' => $before,
        ]);

        return redirect()->route('superadmin.master-data.document-categories.index')->with('success', 'Kategori dokumen berhasil dihapus.');
    }

    private function validateDocumentCategory(Request $request, ?DocumentCategory $category = null): array
    {
        $roleKeys = array_keys(DocumentCategory::ROLE_OPTIONS);
        $asesorScopes = array_keys(DocumentCategory::ASESOR_SCOPE_OPTIONS);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('document_categories', 'code')->ignore($category?->id),
            ],
            'description' => ['nullable', 'string'],
            'required_for_phase' => ['nullable', 'string', 'max:100'],
            'visible_to_roles' => ['nullable', 'array'],
            'visible_to_roles.*' => ['string', Rule::in($roleKeys)],
            'asesor_scope' => ['nullable', 'string', Rule::in($asesorScopes)],
            'template' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg', 'max:10240'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['visible_to_roles'] = array_values($validated['visible_to_roles'] ?? []);

        if (! in_array('asesor', $validated['visible_to_roles'], true)) {
            $validated['asesor_scope'] = null;
        } else {
            $validated['asesor_scope'] = $validated['asesor_scope'] ?? 'all';
        }

        unset($validated['template']);

        return $validated;
    }

    private function storeDocumentCategoryTemplate(Request $request): ?string
    {
        if (! $request->hasFile('template')) {
            return null;
        }

        return $request->file('template')->store('document-category-templates');
    }

    private function documentPhaseOptions(): array
    {
        return [
            '' => 'Semua fase',
            'assessment' => 'Assessment',
            'visitasi' => 'Visitasi',
            'final_validation' => 'Validasi Akhir',
            'hasil' => 'Hasil Akreditasi',
        ];
    }

    private function documentRulePresets(): array
    {
        return [
            [
                'title' => 'Kartu Kendali',
                'code' => 'kartu_kendali',
                'description' => 'Dokumen kontrol dari pesantren; akses utama untuk Pesantren.',
                'roles' => ['pesantren'],
                'scope' => null,
            ],
            [
                'title' => 'Laporan Visitasi Individu',
                'code' => 'laporan_visitasi_individu',
                'description' => 'Laporan personal asesor; dapat diakses Ketua dan Anggota Asesor.',
                'roles' => ['asesor'],
                'scope' => 'all',
            ],
            [
                'title' => 'Laporan Visitasi Kelompok',
                'code' => 'laporan_visitasi_kelompok',
                'description' => 'Laporan final kelompok; akses khusus Ketua Asesor.',
                'roles' => ['asesor'],
                'scope' => 'ketua',
            ],
        ];
    }

    public function roles()
    {
        $roles = Role::with('permissions')->withCount('users')->orderBy('id')->get();
        $permissions = Permission::orderBy('key')->get()->groupBy(fn (Permission $permission) => str($permission->key)->before('.')->toString());
        $totalPermissions = $permissions->flatten()->count();
        $roleStats = [
            'total' => $roles->count(),
            'permissions' => $totalPermissions,
            'assigned_permissions' => $roles->sum(fn (Role $role) => $role->parameter === 'super_admin' ? $totalPermissions : $role->permissions->count()),
            'with_users' => $roles->filter(fn (Role $role) => $role->users_count > 0)->count(),
        ];

        return view('superadmin.master-data.roles.index', compact('roles', 'permissions', 'roleStats', 'totalPermissions'));
    }

    public function updateRolePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
            'reason' => ['required', 'string', 'min:3'],
        ]);

        $oldPermissionIds = $role->permissions()->pluck('permissions.id')->map(fn ($id) => (int) $id)->sort()->values()->all();

        if ($role->parameter === 'super_admin') {
            $newPermissionIds = Permission::pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
            $role->permissions()->sync($newPermissionIds);
        } else {
            $newPermissionIds = collect($validated['permissions'] ?? [])->map(fn ($id) => (int) $id)->sort()->values()->all();
            $role->permissions()->sync($newPermissionIds);
        }

        $this->auditTrail->log('role_permissions_updated', null, auth()->id(), [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'role_parameter' => $role->parameter,
            'old_permission_ids' => $oldPermissionIds,
            'new_permission_ids' => $newPermissionIds,
            'added_permission_ids' => array_values(array_diff($newPermissionIds, $oldPermissionIds)),
            'removed_permission_ids' => array_values(array_diff($oldPermissionIds, $newPermissionIds)),
        ], $validated['reason']);

        return redirect()->route('superadmin.master-data.roles.index')->with('success', 'Permission role berhasil diperbarui.');
    }

    public function users(Request $request)
    {
        $statusOptions = [
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
        ];
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'role' => $request->query('role'),
            'status' => $request->query('status'),
        ];

        if (! array_key_exists((string) $filters['status'], $statusOptions)) {
            $filters['status'] = null;
        }

        $roles = Role::withCount('users')->orderBy('id')->get();
        $query = User::with('role')
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $keyword = '%'.$filters['q'].'%';

                $query->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', $keyword)
                        ->orWhere('email', 'like', $keyword)
                        ->orWhere('uuid', 'like', $keyword)
                        ->orWhere('sso_id', 'like', $keyword)
                        ->orWhere('m_id', 'like', $keyword)
                        ->orWhere('nbm', 'like', $keyword)
                        ->orWhereHas('role', fn ($roleQuery) => $roleQuery->where('name', 'like', $keyword));
                });
            })
            ->when(filled($filters['role']), fn ($query) => $query->where('role_id', $filters['role']))
            ->when(filled($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->orderBy('name');

        $users = $query->get();
        $hasFilters = collect($filters)->contains(fn ($value) => filled($value));
        $userStats = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
            'roles' => $roles->count(),
            'linked_sso' => User::whereNotNull('sso_id')->count(),
        ];

        return view('superadmin.master-data.users.index', compact('users', 'roles', 'filters', 'hasFilters', 'statusOptions', 'userStats'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'm_id' => ['nullable', 'string', 'max:100'],
            'nbm' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::forceCreate([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(Str::random(64)),
            'role_id' => $validated['role_id'],
            'uuid' => (string) Str::uuid(),
            'status' => $validated['status'],
            'm_id' => $validated['m_id'] ?? null,
            'nbm' => $validated['nbm'] ?? null,
        ]);

        $this->auditTrail->log('user_invited', null, auth()->id(), [
            'user_id' => $user->id,
            'new' => $user->only(['name', 'email', 'role_id', 'status', 'm_id', 'nbm']),
        ]);

        return redirect()->route('superadmin.master-data.users.index')->with('success', 'Pengguna berhasil diundang. Akun siap ditautkan saat login Muhammadiyah ID.');
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'reason' => ['required', 'string', 'min:3'],
        ]);

        $before = $user->only(['role_id', 'status']);

        $user->forceFill([
            'role_id' => $validated['role_id'],
            'status' => $validated['status'],
        ])->save();

        $this->auditTrail->log('user_access_updated', null, auth()->id(), [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'old' => $before,
            'new' => $user->fresh()->only(['role_id', 'status']),
        ], $validated['reason']);

        return redirect()->route('superadmin.master-data.users.index')->with('success', 'Akun pengguna berhasil diperbarui.');
    }
}
