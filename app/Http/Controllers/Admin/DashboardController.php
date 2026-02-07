<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Ticket;
use App\Models\Category;
use App\Models\TicketActivity;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display dashboard based on user role
     * 
     * @return \Inertia\Response
     */
    public function index()
    {
        $user = Auth::user();
        $role = $user->roles->first()?->name;

        // Route ke dashboard sesuai role
        switch ($role) {
            case 'super_admin':
            case 'helpdesk':
                return $this->helpdeskDashboard();
            case 'teknisi':
                return $this->teknisiDashboard();
            case 'manager':
            case 'manager_ti':
                return $this->managerDashboard();
            case 'ketua_tim':
                return $this->ketuaTimDashboard();
            default:
                return $this->pegawaiDashboard();
        }
    }

    /**
     * Dashboard Helpdesk (Tier 1) - Most Complex
     * 
     * @return \Inertia\Response
     */
    private function helpdeskDashboard()
    {
        // Count tickets by status dengan trending
        $newCount = Ticket::where('status', 'new')->count();
        $inProgressCount = Ticket::where('status', 'in_progress')->count();
        $pendingCount = Ticket::whereIn('status', ['pending_user', 'pending_external'])->count();
        $resolvedCount = Ticket::where('status', 'resolved')->count();
        $closedCount = Ticket::where('status', 'closed')->count();

        // Hitung trending (perbandingan dengan minggu lalu)
        $oneWeekAgo = Carbon::now()->subWeek();
        $newTrend = $this->calculateTrend('new', $oneWeekAgo);
        $inProgressTrend = $this->calculateTrend('in_progress', $oneWeekAgo);
        $pendingTrend = $this->calculateTrend(['pending_user', 'pending_external'], $oneWeekAgo);
        $resolvedTrend = $this->calculateTrend('resolved', $oneWeekAgo);
        $closedTrend = $this->calculateTrend('closed', $oneWeekAgo);

        // Get tickets perlu triage (status = new, limit 3)
        $triageQueue = Ticket::with(['reporter', 'category', 'userPriority'])
            ->where('status', 'new')
            ->orderBy('created_at', 'asc')
            ->limit(3)
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'title' => $ticket->title,
                    'priority' => $ticket->userPriority?->name,
                    'priority_color' => $ticket->userPriority?->color,
                    'created_at' => $ticket->created_at->diffForHumans(),
                ];
            });

        // Get recent activities (last 5)
        $recentActivities = TicketActivity::with(['ticket', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($activity) {
                return [
                    'description' => $activity->description,
                    'user' => $activity->user?->name,
                    'created_at' => $activity->created_at->diffForHumans(),
                ];
            });

        // Get chart data
        $categoryDistribution = $this->getCategoryDistribution();
        $dailyTrend = $this->getDailyTrend();

        return inertia('Admin/Dashboard/Helpdesk/Index', [
            'stats' => [
                'new' => [
                    'count' => $newCount,
                    'trend' => $newTrend,
                ],
                'inProgress' => [
                    'count' => $inProgressCount,
                    'trend' => $inProgressTrend,
                ],
                'pending' => [
                    'count' => $pendingCount,
                    'trend' => $pendingTrend,
                ],
                'resolved' => [
                    'count' => $resolvedCount,
                    'trend' => $resolvedTrend,
                ],
                'closed' => [
                    'count' => $closedCount,
                    'trend' => $closedTrend,
                ],
            ],
            'triageQueue' => $triageQueue,
            'recentActivities' => $recentActivities,
            'chartData' => [
                'categoryDistribution' => $categoryDistribution,
                'dailyTrend' => $dailyTrend,
            ],
        ]);
    }

    /**
     * Dashboard Pegawai (Simple)
     * 
     * @return \Inertia\Response
     */
    private function pegawaiDashboard()
    {
        $user = Auth::user();

        // Count user's own tickets (sebagai reporter)
        $totalCount = Ticket::where('reporter_id', $user->id)->count();
        $inProgressCount = Ticket::where('reporter_id', $user->id)
            ->where('status', 'in_progress')
            ->count();
        $pendingCount = Ticket::where('reporter_id', $user->id)
            ->whereIn('status', ['pending_user', 'pending_external'])
            ->count();
        $closedCount = Ticket::where('reporter_id', $user->id)
            ->whereIn('status', ['resolved', 'closed']) // Count both resolved and closed
            ->count();

        // Get tickets that need user action (specifically pending_user)
        $actionRequiredTickets = Ticket::with(['category', 'userPriority'])
            ->where('reporter_id', $user->id)
            ->where('status', 'pending_user')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'title' => $ticket->title,
                    'status' => $ticket->status,
                    'status_label' => $this->getStatusLabel($ticket->status),
                    'pending_reason' => $ticket->pending_reason,
                    'created_at' => $ticket->created_at->diffForHumans(),
                    'updated_at' => $ticket->updated_at->diffForHumans(),
                ];
            });

        // Get recent tickets (limit 5)
        $recentTickets = Ticket::with(['category', 'userPriority'])
            ->where('reporter_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'title' => $ticket->title,
                    'status' => $ticket->status,
                    'status_label' => $this->getStatusLabel($ticket->status),
                    'priority' => $ticket->userPriority?->name,
                    'priority_color' => $ticket->userPriority?->color,
                    'created_at' => $ticket->created_at->diffForHumans(),
                ];
            });

        return inertia('Admin/Dashboard/Pegawai/Index', [
            'stats' => [
                'total' => $totalCount,
                'inProgress' => $inProgressCount,
                'pending' => $pendingCount,
                'closed' => $closedCount,
            ],
            'actionRequiredTickets' => $actionRequiredTickets,
            'recentTickets' => $recentTickets,
        ]);
    }


    /**
     * Dashboard Teknisi
     * 
     * @return \Inertia\Response
     */
    private function teknisiDashboard()
    {
        $user = Auth::user();

        // Count assigned tickets
        $assignedCount = Ticket::where('assigned_to_id', $user->id)
            ->where('status', 'assigned')
            ->count();
        $inProgressCount = Ticket::where('assigned_to_id', $user->id)
            ->where('status', 'in_progress')
            ->count();
        $pendingCount = Ticket::where('assigned_to_id', $user->id)
            ->whereIn('status', ['pending_user', 'pending_external'])
            ->count();
        $resolvedCount = Ticket::where('assigned_to_id', $user->id)
            ->where('status', 'resolved')
            ->count();

        // Get all active tickets (not just assigned, but also in_progress and pending)
        // Order by status priority: assigned first, then in_progress, then pending
        $assignedTickets = Ticket::with(['reporter', 'category', 'userPriority'])
            ->where('assigned_to_id', $user->id)
            ->whereIn('status', ['assigned', 'in_progress', 'pending_user', 'pending_external', 'waiting_approval'])
            ->orderByRaw("FIELD(status, 'assigned', 'in_progress', 'pending_user', 'pending_external', 'waiting_approval')")
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'title' => $ticket->title,
                    'status' => $ticket->status,
                    'priority' => $ticket->userPriority?->name,
                    'priority_color' => $ticket->userPriority?->color,
                    'user' => $ticket->reporter?->name,
                    'created_at' => $ticket->created_at->diffForHumans(),
                ];
            });

        return inertia('Admin/Dashboard/Teknisi/Index', [
            'stats' => [
                'assigned' => $assignedCount,
                'inProgress' => $inProgressCount,
                'pending' => $pendingCount,
                'resolved' => $resolvedCount,
            ],
            'assignedTickets' => $assignedTickets,
        ]);
    }


    /**
     * Dashboard Manager TI
     * 
     * @return \Inertia\Response
     */
    private function managerDashboard()
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        // KPI Stats
        $totalTickets = Ticket::where('created_at', '>=', $startOfMonth)->count();
        $resolvedTickets = Ticket::where('status', 'resolved')
            ->where('resolved_at', '>=', $startOfMonth)
            ->count();

        // Get team members count (teknisi)
        $teamMembers = \App\Models\User::role('teknisi')->count();

        // Pending approvals
        $pendingApprovals = Ticket::with(['reporter'])
            ->where('status', 'waiting_approval')
            ->orderBy('created_at', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'title' => $ticket->title,
                    'requester' => $ticket->reporter?->name,
                ];
            });

        // Team performance (top 5 teknisi)
        $teamPerformance = \App\Models\User::role('teknisi')
            ->withCount([
                'assignedTickets as resolved' => function ($query) use ($startOfMonth) {
                    $query->where('status', 'resolved')
                        ->where('resolved_at', '>=', $startOfMonth);
                }
            ])
            ->orderBy('resolved', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'resolved' => $user->resolved ?? 0,
                ];
            });

        return inertia('Admin/Dashboard/Manager/Index', [
            'stats' => [
                'totalTickets' => $totalTickets,
                'resolvedTickets' => $resolvedTickets,
                'avgResolutionTime' => '2.5', // TODO: Calculate actual avg
                'teamMembers' => $teamMembers,
            ],
            'pendingApprovals' => $pendingApprovals,
            'teamPerformance' => $teamPerformance,
        ]);
    }

    /**
     * Dashboard Ketua Tim
     * 
     * @return \Inertia\Response
     */
    private function ketuaTimDashboard()
    {
        $user = Auth::user();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Get team members (same unit/department as ketua tim)
        // Menggunakan unit_id dari user
        $teamMemberIds = \App\Models\User::where('unit_id', $user->unit_id)
            ->where('id', '!=', $user->id)
            ->pluck('id');

        // Stats
        $totalMembers = $teamMemberIds->count();
        $totalTickets = Ticket::whereIn('reporter_id', $teamMemberIds)->count();
        $inProgress = Ticket::whereIn('reporter_id', $teamMemberIds)
            ->where('status', 'in_progress')
            ->count();
        $closedThisMonth = Ticket::whereIn('reporter_id', $teamMemberIds)
            ->where('status', 'closed')
            ->where('closed_at', '>=', $startOfMonth)
            ->count();

        // Team members with ticket stats
        $teamMembers = \App\Models\User::whereIn('id', $teamMemberIds)
            ->withCount([
                'reportedTickets as open' => function ($query) {
                    $query->whereNotIn('status', ['closed', 'resolved']);
                },
                'reportedTickets as closed' => function ($query) {
                    $query->whereIn('status', ['closed', 'resolved']);
                }
            ])
            ->limit(5)
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'position' => $member->jabatan ?? 'Pegawai',
                    'open' => $member->open ?? 0,
                    'closed' => $member->closed ?? 0,
                ];
            });

        // Recent tickets from team
        $recentTickets = Ticket::with(['reporter'])
            ->whereIn('reporter_id', $teamMemberIds)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'title' => $ticket->title,
                    'status' => $ticket->status,
                    'status_label' => $this->getStatusLabel($ticket->status),
                    'reporter' => $ticket->reporter?->name,
                ];
            });

        return inertia('Admin/Dashboard/KetuaTim/Index', [
            'stats' => [
                'totalMembers' => $totalMembers,
                'totalTickets' => $totalTickets,
                'inProgress' => $inProgress,
                'closedThisMonth' => $closedThisMonth,
            ],
            'teamMembers' => $teamMembers,
            'recentTickets' => $recentTickets,
        ]);
    }

    /**
     * Calculate trend percentage
     * 
     * @param string|array $status
     * @param Carbon $since
     * @return array
     */
    private function calculateTrend($status, Carbon $since)
    {
        $query = Ticket::query();

        if (is_array($status)) {
            $query->whereIn('status', $status);
        } else {
            $query->where('status', $status);
        }

        $currentCount = $query->count();

        $previousCount = (clone $query)
            ->where('created_at', '<', $since)
            ->count();

        if ($previousCount === 0) {
            return [
                'value' => $currentCount,
                'percentage' => $currentCount > 0 ? 100 : 0,
                'direction' => $currentCount > 0 ? 'up' : 'neutral',
            ];
        }

        $percentage = (($currentCount - $previousCount) / $previousCount) * 100;

        return [
            'value' => abs(round($percentage)),
            'percentage' => round($percentage, 1),
            'direction' => $percentage > 0 ? 'up' : ($percentage < 0 ? 'down' : 'neutral'),
        ];
    }

    /**
     * Get category distribution for donut chart
     * 
     * @return array
     */
    private function getCategoryDistribution()
    {
        $distribution = Ticket::join('categories', 'tickets.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('count(*) as total'))
            ->groupBy('categories.name')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        $labels = [];
        $data = [];
        $colors = [
            'rgba(59, 130, 246, 0.8)',   // Blue
            'rgba(34, 197, 94, 0.8)',    // Green
            'rgba(249, 115, 22, 0.8)',   // Orange
            'rgba(168, 85, 247, 0.8)',   // Purple
            'rgba(107, 114, 128, 0.8)',  // Gray
        ];

        foreach ($distribution as $index => $item) {
            $labels[] = $item->name;
            $data[] = $item->total;
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => array_slice($colors, 0, count($labels)),
        ];
    }

    /**
     * Get daily trend for line chart (last 7 days)
     * 
     * @return array
     */
    private function getDailyTrend()
    {
        $days = [];
        $newTickets = [];
        $resolvedTickets = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayName = $date->locale('id')->isoFormat('ddd');

            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();

            $newCount = Ticket::whereBetween('created_at', [$dayStart, $dayEnd])
                ->count();

            $resolvedCount = Ticket::where('status', 'resolved')
                ->whereBetween('updated_at', [$dayStart, $dayEnd])
                ->count();

            $days[] = $dayName;
            $newTickets[] = $newCount;
            $resolvedTickets[] = $resolvedCount;
        }

        return [
            'days' => $days,
            'new' => $newTickets,
            'resolved' => $resolvedTickets,
        ];
    }

    /**
     * Get status label in Indonesian
     * 
     * @param string $status
     * @return string
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'new' => 'Baru',
            'triage' => 'Triage',
            'assigned' => 'Ditugaskan',
            'in_progress' => 'Dalam Proses',
            'pending_user' => 'Pending User',
            'pending_external' => 'Pending Vendor',
            'waiting_approval' => 'Menunggu Approval',
            'resolved' => 'Selesai',
            'closed' => 'Ditutup',
            'reopened' => 'Dibuka Kembali',
        ];

        return $labels[$status] ?? $status;
    }
}
