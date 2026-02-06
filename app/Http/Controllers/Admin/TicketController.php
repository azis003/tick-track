<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ticket;
use App\Models\Category;
use App\Models\Priority;
use App\Models\User;
use App\Services\TicketService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class TicketController extends Controller implements HasMiddleware
{
    public function __construct(
        protected TicketService $ticketService
    ) {
    }

    /**
     * Define permission middleware
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:tickets.view-all', only: ['index']),
            new Middleware('permission:tickets.view-own', only: ['myTickets', 'show']),
            new Middleware('permission:tickets.view-unit', only: ['unitTickets']),
            new Middleware('permission:tickets.create', only: ['create', 'store']),
        ];
    }

    /**
     * List semua tiket (Helpdesk, Manager, Admin)
     */
    public function index(Request $request): Response
    {
        $tickets = $this->ticketService->getTicketsForUser(
            $request->user(),
            $request->only(['search', 'status', 'category_id', 'priority_id'])
        );

        return Inertia::render('Admin/Tickets/Index', [
            'tickets' => $tickets,
            'filters' => $request->only(['search', 'status', 'category_id', 'priority_id']),
            'categories' => Category::where('is_active', true)->get(['id', 'name']),
            'priorities' => Priority::where('is_active', true)->get(['id', 'name', 'color']),
            'statuses' => Ticket::STATUS_LABELS,
        ]);
    }

    /**
     * Tiket milik user yang login (Pegawai view)
     */
    public function myTickets(Request $request): Response
    {
        $tickets = $this->ticketService->getMyTickets(
            $request->user(),
            $request->only(['search', 'status'])
        );

        return Inertia::render('Admin/Tickets/MyTickets', [
            'tickets' => $tickets,
            'filters' => $request->only(['search', 'status']),
            'statuses' => Ticket::STATUS_LABELS,
        ]);
    }

    /**
     * Tiket anggota unit (Ketua Tim view)
     */
    public function unitTickets(Request $request): Response
    {
        $tickets = $this->ticketService->getUnitTickets(
            $request->user(),
            $request->only(['search', 'status'])
        );

        return Inertia::render('Admin/Tickets/UnitTickets', [
            'tickets' => $tickets,
            'filters' => $request->only(['search', 'status']),
            'statuses' => Ticket::STATUS_LABELS,
        ]);
    }

    /**
     * Form buat tiket baru
     */
    public function create(): Response
    {
        $user = auth()->user();
        $isHelpdesk = $user->hasRole('helpdesk');

        $data = [
            'categories' => Category::where('is_active', true)->get(['id', 'name', 'icon', 'color']),
            'priorities' => Priority::where('is_active', true)->orderBy('level')->get(['id', 'name', 'color']),
            'isHelpdesk' => $isHelpdesk,
        ];

        // Helpdesk bisa memilih reporter dari daftar user
        if ($isHelpdesk) {
            $data['users'] = User::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        }

        return Inertia::render('Admin/Tickets/Create', $data);
    }

    /**
     * Simpan tiket baru
     */
    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $ticket = $this->ticketService->createTicket(
            $request->validated(),
            $request->user(),
            $request->file('attachments', [])
        );

        return redirect('/admin/tickets/my-tickets')
            ->with('success', 'Tiket berhasil dibuat dengan nomor ' . $ticket->ticket_number);
    }

    /**
     * Detail tiket
     */
    public function show(Ticket $ticket): Response
    {
        // Authorization: check if user can view this ticket
        $user = auth()->user();
        $canView = $user->can('tickets.view-all')
            || $ticket->reporter_id === $user->id
            || $ticket->created_by_id === $user->id
            || $ticket->assigned_to_id === $user->id;

        if (!$canView) {
            abort(403, 'Anda tidak memiliki akses ke tiket ini.');
        }

        $ticket->load([
            'reporter.unit',
            'createdBy',
            'category',
            'userPriority',
            'finalPriority',
            'assignedTo',
            'assignedBy',
            'logs.user',
            'comments.user',
            'attachments.user',
        ]);

        // Add computed attributes
        $ticket->status_label = $ticket->statusLabel;
        $ticket->status_color = $ticket->statusColor;

        return Inertia::render('Admin/Tickets/Show', [
            'ticket' => $ticket,
            'statuses' => Ticket::STATUS_LABELS,
            'statusColors' => Ticket::STATUS_COLORS,
        ]);
    }
}
