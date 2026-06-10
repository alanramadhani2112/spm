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

        return view('superadmin.master-data.document-categories.index', compact('categories'));
    }

    public function storeDocumentCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'required_for_phase' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DocumentCategory::create($validated + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('superadmin.master-data.document-categories.index')->with('success', 'Kategori dokumen berhasil ditambahkan.');
    }

    public function updateDocumentCategory(Request $request, DocumentCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'required_for_phase' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update($validated + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('superadmin.master-data.document-categories.index')->with('success', 'Kategori dokumen berhasil diperbarui.');
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

    public function roles()
    {
        $roles = Role::with('permissions')->orderBy('id')->get();
        $permissions = Permission::orderBy('key')->get()->groupBy(fn (Permission $permission) => str($permission->key)->before('.')->toString());

        return view('superadmin.master-data.roles.index', compact('roles', 'permissions'));
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

    public function users()
    {
        $users = User::with('role')->orderBy('name')->get();
        $roles = Role::orderBy('id')->get();

        return view('superadmin.master-data.users.index', compact('users', 'roles'));
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
