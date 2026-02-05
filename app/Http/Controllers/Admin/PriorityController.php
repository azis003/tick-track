<?php

namespace App\Http\Controllers\Admin;

use App\Models\Priority;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class PriorityController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware(['permission:priorities.index'], only: ['index']),
            new Middleware(['permission:priorities.create'], only: ['create', 'store']),
            new Middleware(['permission:priorities.edit'], only: ['edit', 'update']),
            new Middleware(['permission:priorities.delete'], only: ['destroy']),
        ];
    }

    /**
     * Display a listing of priorities
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $priorities = Priority::query()
            ->withCount(['userPriorityTickets', 'finalPriorityTickets'])
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('level', 'desc') // Level tertinggi dulu (Critical > High > Medium > Low)
            ->paginate(10)
            ->withQueryString();

        return inertia('Admin/Priorities/Index', [
            'priorities' => $priorities,
            'filters' => [
                'search' => $request->search ?? '',
            ],
        ]);
    }

    /**
     * Show the form for creating a new priority
     *
     * @return Response
     */
    public function create(): Response
    {
        return inertia('Admin/Priorities/Create');
    }

    /**
     * Store a newly created priority
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:priorities,name',
            'level' => 'required|integer|min:1|unique:priorities,level',
            'color' => 'required|string|max:20',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Nama prioritas wajib diisi.',
            'name.unique' => 'Nama prioritas sudah terdaftar.',
            'name.max' => 'Nama prioritas maksimal 50 karakter.',
            'level.required' => 'Level prioritas wajib diisi.',
            'level.unique' => 'Level prioritas sudah digunakan.',
            'level.min' => 'Level prioritas minimal 1.',
            'color.required' => 'Warna prioritas wajib diisi.',
            'color.max' => 'Warna maksimal 20 karakter.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
        ]);

        Priority::create([
            'name' => $request->name,
            'level' => $request->level,
            'color' => $request->color,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()
            ->route('admin.priorities.index')
            ->with('success', 'Prioritas berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified priority
     *
     * @param Priority $priority
     * @return Response
     */
    public function edit(Priority $priority): Response
    {
        $priority->loadCount(['userPriorityTickets', 'finalPriorityTickets']);

        return inertia('Admin/Priorities/Edit', [
            'priority' => $priority,
        ]);
    }

    /**
     * Update the specified priority
     *
     * @param Request $request
     * @param Priority $priority
     * @return RedirectResponse
     */
    public function update(Request $request, Priority $priority): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:priorities,name,' . $priority->id,
            'level' => 'required|integer|min:1|unique:priorities,level,' . $priority->id,
            'color' => 'required|string|max:20',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Nama prioritas wajib diisi.',
            'name.unique' => 'Nama prioritas sudah terdaftar.',
            'name.max' => 'Nama prioritas maksimal 50 karakter.',
            'level.required' => 'Level prioritas wajib diisi.',
            'level.unique' => 'Level prioritas sudah digunakan.',
            'level.min' => 'Level prioritas minimal 1.',
            'color.required' => 'Warna prioritas wajib diisi.',
            'color.max' => 'Warna maksimal 20 karakter.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
        ]);

        $priority->update([
            'name' => $request->name,
            'level' => $request->level,
            'color' => $request->color,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()
            ->route('admin.priorities.index')
            ->with('success', 'Prioritas berhasil diperbarui.');
    }

    /**
     * Remove the specified priority
     *
     * @param Priority $priority
     * @return RedirectResponse
     */
    public function destroy(Priority $priority): RedirectResponse
    {
        // Cek apakah ada tiket dengan priority ini (baik user_priority maupun final_priority)
        $userPriorityCount = $priority->userPriorityTickets()->count();
        $finalPriorityCount = $priority->finalPriorityTickets()->count();
        $totalTickets = $userPriorityCount + $finalPriorityCount;

        if ($totalTickets > 0) {
            return redirect()
                ->route('admin.priorities.index')
                ->with('error', 'Prioritas tidak dapat dihapus karena masih digunakan oleh ' . $totalTickets . ' tiket.');
        }

        $priority->delete();

        return redirect()
            ->route('admin.priorities.index')
            ->with('success', 'Prioritas berhasil dihapus.');
    }
}
