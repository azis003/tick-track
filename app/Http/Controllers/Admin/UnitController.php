<?php

namespace App\Http\Controllers\Admin;

use App\Models\Unit;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class UnitController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware(['permission:units.index'], only: ['index']),
            new Middleware(['permission:units.create'], only: ['create', 'store']),
            new Middleware(['permission:units.edit'], only: ['edit', 'update']),
            new Middleware(['permission:units.delete'], only: ['destroy']),
        ];
    }

    /**
     * Display a listing of units
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $units = Unit::query()
            ->with(['head:id,name', 'members:id,name,unit_id'])
            ->withCount('members')
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return inertia('Admin/Units/Index', [
            'units' => $units,
            'filters' => [
                'search' => $request->search ?? '',
            ],
        ]);
    }

    /**
     * Show the form for creating a new unit
     *
     * @return Response
     */
    public function create(): Response
    {
        // Get users dengan role ketua_tim untuk pilihan Ketua Tim
        $users = User::select('id', 'name', 'email')
            ->where('is_active', true)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'ketua_tim');
            })
            ->orderBy('name')
            ->get();

        return inertia('Admin/Units/Create', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created unit
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'description' => 'nullable|string|max:500',
            'head_user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Nama unit wajib diisi.',
            'name.unique' => 'Nama unit sudah terdaftar.',
            'name.max' => 'Nama unit maksimal 255 karakter.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
            'head_user_id.exists' => 'Ketua Tim yang dipilih tidak valid.',
        ]);

        Unit::create([
            'name' => $request->name,
            'description' => $request->description,
            'head_user_id' => $request->head_user_id,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit Kerja berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified unit
     *
     * @param Unit $unit
     * @return Response
     */
    public function edit(Unit $unit): Response
    {
        $unit->load(['head:id,name,email', 'members:id,name,email,unit_id']);

        // Get users dengan role ketua_tim untuk pilihan Ketua Tim
        $users = User::select('id', 'name', 'email')
            ->where('is_active', true)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'ketua_tim');
            })
            ->orderBy('name')
            ->get();

        return inertia('Admin/Units/Edit', [
            'unit' => $unit,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified unit
     *
     * @param Request $request
     * @param Unit $unit
     * @return RedirectResponse
     */
    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:units,name,' . $unit->id,
            'description' => 'nullable|string|max:500',
            'head_user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Nama unit wajib diisi.',
            'name.unique' => 'Nama unit sudah terdaftar.',
            'name.max' => 'Nama unit maksimal 255 karakter.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
            'head_user_id.exists' => 'Ketua Tim yang dipilih tidak valid.',
        ]);

        $unit->update([
            'name' => $request->name,
            'description' => $request->description,
            'head_user_id' => $request->head_user_id,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit Kerja berhasil diperbarui.');
    }

    /**
     * Remove the specified unit
     *
     * @param Unit $unit
     * @return RedirectResponse
     */
    public function destroy(Unit $unit): RedirectResponse
    {
        // Cek apakah ada anggota di unit ini
        if ($unit->members()->count() > 0) {
            return redirect()
                ->route('units.index')
                ->with('error', 'Unit tidak dapat dihapus karena masih memiliki ' . $unit->members()->count() . ' anggota.');
        }

        $unit->delete();

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit Kerja berhasil dihapus.');
    }
}
