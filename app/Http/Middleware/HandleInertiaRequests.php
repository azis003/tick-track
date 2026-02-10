<?php

namespace App\Http\Middleware;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),

            // 1. Flash Messages: untuk toast/alert
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
                'warning' => fn() => $request->session()->get('warning'),
            ],

            // 2. AUTH: Data user yang sedang login
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'nip' => $user->nip,
                    'avatar' => $user->avatar,
                    'unit' => $user->unit?->name,
                    'roles' => $user->getRoleNames(),
                ] : null,
                'permissions' => $user ? $user->getPermissionArray() : [],
                'pending_user_count' => ($user && $user->hasRole('pegawai'))
                    ? Ticket::where('reporter_id', $user->id)->where('status', Ticket::STATUS_PENDING_USER)->count()
                    : 0,
                'task_queue_count' => fn() => $user ? $this->getTaskQueueCount($user) : 0,
            ],

            // 3. CONFIG: Informasi global aplikasi
            'app' => [
                'name' => config('app.name'),
                'url' => config('app.url'),
            ],

        ];
    }

    /**
     * Hitung jumlah tiket yang memerlukan tindakan dari user saat ini.
     * Logika sama dengan TicketService::getTaskQueue tapi hanya count.
     */
    private function getTaskQueueCount($user): int
    {
        $query = Ticket::query();
        $conditions = [];

        // 1. Helpdesk: tiket new/reopened perlu verifikasi
        if ($user->can('tickets.triage')) {
            $conditions[] = function ($q) {
                $q->whereIn('status', [Ticket::STATUS_NEW, Ticket::STATUS_REOPENED]);
            };
        }

        // 2. Teknisi: tiket yang ditugaskan perlu dikerjakan
        if ($user->can('tickets.work')) {
            $conditions[] = function ($q) use ($user) {
                $q->where('assigned_to_id', $user->id)
                    ->whereIn('status', [
                        Ticket::STATUS_ASSIGNED,
                        Ticket::STATUS_IN_PROGRESS,
                        Ticket::STATUS_PENDING_EXTERNAL,
                    ]);
            };
        }

        // 3. Manager: tiket menunggu approval
        if ($user->can('tickets.approve')) {
            $conditions[] = function ($q) {
                $q->where('status', Ticket::STATUS_WAITING_APPROVAL);
            };
        }

        // 4. Creator: tiket pending response atau perlu konfirmasi penyelesaian
        $conditions[] = function ($q) use ($user) {
            $q->where('created_by_id', $user->id)
                ->whereIn('status', [Ticket::STATUS_PENDING_USER, Ticket::STATUS_RESOLVED]);
        };

        // Apply OR conditions
        if (!empty($conditions)) {
            $query->where(function ($mainQuery) use ($conditions) {
                foreach ($conditions as $index => $condition) {
                    if ($index === 0) {
                        $mainQuery->where($condition);
                    } else {
                        $mainQuery->orWhere($condition);
                    }
                }
            });
        }

        return $query->count();
    }
}
