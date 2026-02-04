<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        // Get permissions with search and pagination
        $permissions = Permission::when($request->search, function ($query, $search) {
            return $query->where('name', 'like', '%' . $search . '%');
        })
            ->withCount('roles') // Count roles using this permission
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return inertia('Admin/Permissions/Index', [
            'permissions' => $permissions,
            'filters' => [
                'search' => $request->search ?? '',
            ],
        ]);
    }

    /**
     * Show the form for creating a new permission
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return inertia('Admin/Permissions/Create');
    }

    /**
     * Store a newly created permission
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate request - format: module.action
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:permissions,name',
                'regex:/^[a-z]+(\.[a-z]+(-[a-z]+)*)+$/', // module.action format
            ],
        ], [
            'name.required' => 'Nama permission wajib diisi.',
            'name.unique' => 'Permission dengan nama ini sudah ada.',
            'name.regex' => 'Nama permission harus dalam format module.action (contoh: tickets.create, reports.view-all).',
            'name.max' => 'Nama permission maksimal 255 karakter.',
        ]);

        // Create permission
        Permission::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified permission
     *
     * @param Permission $permission
     * @return \Inertia\Response
     */
    public function edit(Permission $permission)
    {
        return inertia('Admin/Permissions/Edit', [
            'permission' => $permission,
        ]);
    }

    /**
     * Update the specified permission
     *
     * @param Request $request
     * @param Permission $permission
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Permission $permission)
    {
        // Validate request - format: module.action
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:permissions,name,' . $permission->id,
                'regex:/^[a-z]+(\.[a-z]+(-[a-z]+)*)+$/',
            ],
        ], [
            'name.required' => 'Nama permission wajib diisi.',
            'name.unique' => 'Permission dengan nama ini sudah ada.',
            'name.regex' => 'Nama permission harus dalam format module.action (contoh: tickets.create, reports.view-all).',
            'name.max' => 'Nama permission maksimal 255 karakter.',
        ]);

        // Update permission
        $permission->update([
            'name' => $request->name,
        ]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission berhasil diperbarui.');
    }

    /**
     * Remove the specified permission
     *
     * @param Permission $permission
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Permission $permission)
    {
        // Prevent deletion of system critical permissions
        $protectedPermissions = [
            'roles.index',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'permissions.index',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',
            'users.index',
            'users.create',
            'users.edit',
            'users.delete',
            'system.logs',
            'system.config',
        ];

        if (in_array($permission->name, $protectedPermissions)) {
            return redirect()
                ->route('admin.permissions.index')
                ->with('error', 'Permission sistem tidak dapat dihapus.');
        }

        // Check if permission is being used by any role
        if ($permission->roles()->count() > 0) {
            return redirect()
                ->route('admin.permissions.index')
                ->with('error', 'Permission masih digunakan oleh ' . $permission->roles()->count() . ' role. Hapus dari role terlebih dahulu.');
        }

        // Delete permission
        $permission->delete();

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission berhasil dihapus.');
    }
}
