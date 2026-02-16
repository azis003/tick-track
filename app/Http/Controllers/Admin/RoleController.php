<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Get protected roles from config
     */
    protected function getProtectedRoles(): array
    {
        return config('permissions.protected_roles', ['super_admin']);
    }

    /**
     * Get group labels from config
     */
    protected function getGroupLabels(): array
    {
        return config('permissions.group_labels', []);
    }

    /**
     * Display a listing of roles
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        // Get roles with search and pagination
        $roles = Role::when($request->search, function ($query, $search) {
            return $query->where('name', 'like', '%' . $search . '%');
        })
            ->withCount(['permissions', 'users'])
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return inertia('Admin/Roles/Index', [
            'roles' => $roles,
            'filters' => [
                'search' => $request->search ?? '',
            ],
            'protectedRoles' => $this->getProtectedRoles(),
        ]);
    }

    /**
     * Show the form for creating a new role
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        // Get all permissions for auto-grouping in frontend
        $permissions = Permission::select('id', 'name')
            ->orderBy('name')
            ->get();

        return inertia('Admin/Roles/Create', [
            'permissions' => $permissions,
            'groupLabels' => $this->getGroupLabels(),
        ]);
    }

    /**
     * Store a newly created role
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate request
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:roles,name',
                'regex:/^[a-z]+(_[a-z]+)*$/', // snake_case only
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'name.required' => 'Nama role wajib diisi.',
            'name.unique' => 'Role dengan nama ini sudah ada.',
            'name.regex' => 'Nama role harus dalam format snake_case (huruf kecil, pisah dengan _).',
            'name.max' => 'Nama role maksimal 255 karakter.',
        ]);

        // Create role
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        // Sync permissions by ID
        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role berhasil ditambahkan dengan ' . count($request->permissions ?? []) . ' permission.');
    }

    /**
     * Show the form for editing the specified role
     *
     * @param Role $role
     * @return \Inertia\Response
     */
    public function edit(Role $role)
    {
        // Get all permissions for auto-grouping in frontend
        $permissions = Permission::select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get role's current permission IDs
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return inertia('Admin/Roles/Edit', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
            'groupLabels' => $this->getGroupLabels(),
            'isProtected' => in_array($role->name, $this->getProtectedRoles()),
        ]);
    }

    /**
     * Update the specified role
     *
     * @param Request $request
     * @param Role $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Role $role)
    {
        // Check if trying to rename protected role
        $isProtected = in_array($role->name, $this->getProtectedRoles());

        // Validate request
        $rules = [
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ];

        // Only validate name if not protected
        if (!$isProtected) {
            $rules['name'] = [
                'required',
                'string',
                'max:255',
                'unique:roles,name,' . $role->id,
                'regex:/^[a-z]+(_[a-z]+)*$/',
            ];
        }

        $request->validate($rules, [
            'name.required' => 'Nama role wajib diisi.',
            'name.unique' => 'Role dengan nama ini sudah ada.',
            'name.regex' => 'Nama role harus dalam format snake_case (huruf kecil, pisah dengan _).',
            'name.max' => 'Nama role maksimal 255 karakter.',
        ]);

        // Update role name (if not protected)
        if (!$isProtected && $request->name) {
            $role->update([
                'name' => $request->name,
            ]);
        }

        // Sync permissions by ID
        $role->syncPermissions($request->permissions ?? []);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role berhasil diperbarui dengan ' . count($request->permissions ?? []) . ' permission.');
    }

    /**
     * Remove the specified role
     *
     * @param Role $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Role $role)
    {
        // Prevent deletion of protected roles
        if (in_array($role->name, $this->getProtectedRoles())) {
            return redirect()
                ->route('roles.index')
                ->with('error', 'Role "' . $role->name . '" adalah role sistem dan tidak dapat dihapus.');
        }

        // Check if role is being used by any user
        if ($role->users()->count() > 0) {
            return redirect()
                ->route('roles.index')
                ->with('error', 'Role masih digunakan oleh ' . $role->users()->count() . ' user. Ubah role user terlebih dahulu.');
        }

        // Delete role
        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role berhasil dihapus.');
    }
}
