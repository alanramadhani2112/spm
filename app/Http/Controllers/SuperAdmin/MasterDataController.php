<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use App\Models\MasterEdpmButir;
use App\Models\MasterEdpmKomponen;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MasterDataController extends Controller
{
    public function index()
    {
        $stats = [
            'komponen' => MasterEdpmKomponen::count(),
            'butir' => MasterEdpmButir::count(),
            'dokumen' => DocumentCategory::count(),
            'roles' => Role::count(),
            'users' => User::count(),
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

        MasterEdpmKomponen::create([
            'kode' => $validated['kode'],
            'name' => $validated['nama'],
            'nama' => $validated['nama'],
        ]);

        return redirect()->route('superadmin.master-data.edpm.index')->with('success', 'Komponen EDPM berhasil ditambahkan.');
    }

    public function updateKomponen(Request $request, MasterEdpmKomponen $komponen)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:100', Rule::unique('master_edpm_komponens', 'kode')->ignore($komponen->id)],
            'nama' => ['required', 'string', 'max:255'],
        ]);

        $komponen->update([
            'kode' => $validated['kode'],
            'name' => $validated['nama'],
            'nama' => $validated['nama'],
        ]);

        return redirect()->route('superadmin.master-data.edpm.index')->with('success', 'Komponen EDPM berhasil diperbarui.');
    }

    public function destroyKomponen(MasterEdpmKomponen $komponen)
    {
        $komponen->delete();

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

        MasterEdpmButir::create([
            'komponen_id' => $validated['komponen_id'],
            'kode' => $validated['kode'] ?? null,
            'name' => $validated['nama'],
            'nama' => $validated['nama'],
            'deskripsi' => $validated['deskripsi'] ?? null,
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

        $butir->update([
            'komponen_id' => $validated['komponen_id'],
            'kode' => $validated['kode'] ?? null,
            'name' => $validated['nama'],
            'nama' => $validated['nama'],
            'deskripsi' => $validated['deskripsi'] ?? null,
        ]);

        return redirect()->route('superadmin.master-data.edpm.index')->with('success', 'Butir EDPM berhasil diperbarui.');
    }

    public function destroyButir(MasterEdpmButir $butir)
    {
        $butir->delete();

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

    public function storeDocumentCategory(Request $request)
    {
        $validated = $this->validateDocumentCategory($request);
        $validated['code'] = $validated['code'] ?: Str::slug($validated['name'], '_');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['template_path'] = $this->storeDocumentCategoryTemplate($request);

        DocumentCategory::create($validated);

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

        $category->update($validated);

        return redirect()->route('superadmin.master-data.document-categories.index')->with('success', 'Aturan kategori dokumen berhasil diperbarui.');
    }

    public function toggleDocumentCategory(DocumentCategory $category)
    {
        $category->update(['is_active' => ! $category->is_active]);

        return redirect()->route('superadmin.master-data.document-categories.index')->with('success', 'Status kategori dokumen berhasil diubah.');
    }

    public function destroyDocumentCategory(DocumentCategory $category)
    {
        $category->delete();

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
        ]);

        if ($role->parameter === 'super_admin') {
            $role->permissions()->sync(Permission::pluck('id')->all());
        } else {
            $role->permissions()->sync($validated['permissions'] ?? []);
        }

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

        User::forceCreate([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(Str::random(64)),
            'role_id' => $validated['role_id'],
            'uuid' => (string) Str::uuid(),
            'status' => $validated['status'],
            'm_id' => $validated['m_id'] ?? null,
            'nbm' => $validated['nbm'] ?? null,
        ]);

        return redirect()->route('superadmin.master-data.users.index')->with('success', 'Pengguna berhasil diundang. Akun siap ditautkan saat login Muhammadiyah ID.');
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ]);

        $user->forceFill($validated)->save();

        return redirect()->route('superadmin.master-data.users.index')->with('success', 'Akun pengguna berhasil diperbarui.');
    }
}
