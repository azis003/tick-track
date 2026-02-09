<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ticket;
use App\Models\Category;
use App\Models\Priority;
use App\Models\User;
use App\Services\TicketService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\TriageTicketRequest;
use App\Http\Requests\AssignTicketRequest;
use App\Http\Requests\ReturnTicketRequest;
use App\Http\Requests\SetPendingRequest;
use App\Http\Requests\ResolveTicketRequest;
use App\Http\Requests\RequestApprovalRequest;
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
            // Phase 1: View & Create
            new Middleware('permission:tickets.view-all', only: ['index']),
            new Middleware('permission:tickets.view-own', only: ['myTickets', 'show']),
            new Middleware('permission:tickets.view-unit', only: ['unitTickets']),
            new Middleware('permission:tickets.create', only: ['create', 'store']),
            // Phase 2: Triage & Assignment
            new Middleware('permission:tickets.triage', only: ['triage', 'processTriage', 'selfHandle']),
            new Middleware('permission:tickets.assign', only: ['assign']),
            new Middleware('permission:tickets.accept', only: ['accept']),
            new Middleware('permission:tickets.return', only: ['returnTicket']),
            new Middleware('permission:tickets.view-assigned', only: ['assignedTickets']),
            // Phase 3: Work & Resolve
            new Middleware('permission:tickets.work', only: ['setPending']),
            new Middleware('permission:tickets.resolve', only: ['resolve']),
            new Middleware('permission:approvals.request', only: ['requestApproval']),
            // Phase 4: Close & Reopen
            new Middleware('permission:tickets.close', only: ['close']),
            new Middleware('permission:tickets.reopen', only: ['reopen']),
        ];
    }

    // ==============================================
    // PHASE 1: BASIC CRUD
    // ==============================================

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
     * Tiket Saya - tickets created by current user
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
     * Daftar Tugas - tickets requiring action from current user
     */
    public function taskQueue(Request $request): Response
    {
        $user = $request->user();

        $tickets = $this->ticketService->getTaskQueue(
            $user,
            $request->only(['search', 'status'])
        );

        // Determine available statuses based on user role
        $availableStatuses = [];

        if ($user->can('tickets.triage')) {
            $availableStatuses[Ticket::STATUS_NEW] = Ticket::STATUS_LABELS[Ticket::STATUS_NEW];
            $availableStatuses[Ticket::STATUS_REOPENED] = Ticket::STATUS_LABELS[Ticket::STATUS_REOPENED];
        }

        if ($user->can('tickets.work')) {
            $availableStatuses[Ticket::STATUS_ASSIGNED] = Ticket::STATUS_LABELS[Ticket::STATUS_ASSIGNED];
            $availableStatuses[Ticket::STATUS_IN_PROGRESS] = Ticket::STATUS_LABELS[Ticket::STATUS_IN_PROGRESS];
            $availableStatuses[Ticket::STATUS_PENDING_EXTERNAL] = Ticket::STATUS_LABELS[Ticket::STATUS_PENDING_EXTERNAL];
        }

        if ($user->can('tickets.approve')) {
            $availableStatuses[Ticket::STATUS_WAITING_APPROVAL] = Ticket::STATUS_LABELS[Ticket::STATUS_WAITING_APPROVAL];
        }

        // Creator tasks
        $availableStatuses[Ticket::STATUS_PENDING_USER] = Ticket::STATUS_LABELS[Ticket::STATUS_PENDING_USER];
        $availableStatuses[Ticket::STATUS_RESOLVED] = Ticket::STATUS_LABELS[Ticket::STATUS_RESOLVED];

        return Inertia::render('Admin/Tickets/TaskQueue', [
            'tickets' => $tickets,
            'filters' => $request->only(['search', 'status']),
            'statuses' => $availableStatuses,
            'priorities' => Priority::orderBy('level')->get(),
            'technicians' => $this->getTechnicians(),
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

        return redirect()->route('admin.tickets.my-tickets')
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
            'comments.attachments',
            'attachments.user',
        ]);

        // Add computed attributes
        $ticket->status_label = $ticket->statusLabel;
        $ticket->status_color = $ticket->statusColor;

        // Get additional data for triage actions
        $data = [
            'ticket' => $ticket,
            'statuses' => Ticket::STATUS_LABELS,
            'statusColors' => Ticket::STATUS_COLORS,
        ];

        // If user can triage, include priorities and technicians
        if ($user->can('tickets.triage')) {
            $data['priorities'] = Priority::where('is_active', true)
                ->orderBy('level')
                ->get(['id', 'name', 'color']);
            $data['technicians'] = $this->ticketService->getAvailableTechnicians();
        }

        return Inertia::render('Admin/Tickets/Show', $data);
    }

    // ==============================================
    // PHASE 2: TRIAGE & ASSIGNMENT
    // ==============================================

    /**
     * List tiket yang perlu triage (Helpdesk)
     */
    public function triage(Request $request): Response
    {
        $tickets = $this->ticketService->getTicketsNeedTriage(
            $request->only(['search', 'category_id'])
        );

        return Inertia::render('Admin/Tickets/Triage', [
            'tickets' => $tickets,
            'filters' => $request->only(['search', 'category_id']),
            'categories' => Category::where('is_active', true)->get(['id', 'name']),
        ]);
    }

    /**
     * Process triage - set final priority
     */
    public function processTriage(TriageTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        // Only allow triage for new or reopened tickets
        if (!in_array($ticket->status, [Ticket::STATUS_NEW, Ticket::STATUS_REOPENED])) {
            return back()->with('error', 'Tiket ini tidak dapat di-triage.');
        }

        $user = $request->user();
        $action = $request->action;

        // Step 1: Triage the ticket (set final priority)
        $this->ticketService->triageTicket(
            $ticket,
            $request->final_priority_id,
            $request->notes,
            $user
        );

        // Step 2: Immediately assign or self-handle based on action
        if ($action === 'assign') {
            $this->ticketService->assignTicket(
                $ticket,
                $request->technician_id,
                $user,
                $request->notes
            );
            return redirect()->route('admin.tickets.task-queue')
                ->with('success', 'Tiket berhasil diverifikasi dan ditugaskan ke teknisi.');
        } else {
            // self_handle
            $this->ticketService->startSelfHandle($ticket, $user);
            return redirect()->route('admin.tickets.task-queue')
                ->with('success', 'Tiket berhasil diverifikasi. Anda mulai mengerjakan tiket ini.');
        }
    }

    /**
     * Assign ticket to technician (kept for backward compatibility / reassign)
     */
    public function assign(AssignTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        // Allow assign for triaged tickets OR reassign for assigned tickets
        if (!in_array($ticket->status, [Ticket::STATUS_TRIAGE, Ticket::STATUS_ASSIGNED])) {
            return back()->with('error', 'Tiket harus di-triage terlebih dahulu sebelum ditugaskan.');
        }

        $this->ticketService->assignTicket(
            $ticket,
            $request->technician_id,
            $request->user(),
            $request->notes
        );

        return back()->with('success', 'Tiket berhasil ditugaskan ke teknisi.');
    }

    /**
     * Self-handle by Helpdesk (kept for backward compatibility)
     */
    public function selfHandle(Ticket $ticket): RedirectResponse
    {
        // Allow self-handle for triaged tickets
        if ($ticket->status !== Ticket::STATUS_TRIAGE) {
            return back()->with('error', 'Tiket harus di-triage terlebih dahulu.');
        }

        $this->ticketService->startSelfHandle($ticket, auth()->user());

        return back()->with('success', 'Anda mulai mengerjakan tiket ini.');
    }

    /**
     * Technician accepts and starts working on ticket
     */
    public function accept(Ticket $ticket): RedirectResponse
    {
        $user = auth()->user();

        // Check if ticket is assigned to this technician
        if ($ticket->assigned_to_id !== $user->id) {
            return back()->with('error', 'Tiket ini bukan ditugaskan kepada Anda.');
        }

        // Only allow accept for assigned tickets
        if ($ticket->status !== Ticket::STATUS_ASSIGNED) {
            return back()->with('error', 'Tiket ini tidak dalam status ditugaskan.');
        }

        $this->ticketService->acceptAndStart($ticket, $user);

        return back()->with('success', 'Anda mulai mengerjakan tiket ini.');
    }

    /**
     * Technician returns ticket to Helpdesk
     */
    public function returnTicket(ReturnTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $user = auth()->user();

        // Check if ticket is assigned to this technician
        if ($ticket->assigned_to_id !== $user->id) {
            return back()->with('error', 'Tiket ini bukan ditugaskan kepada Anda.');
        }

        // Only allow return for assigned or in_progress tickets
        if (!in_array($ticket->status, [Ticket::STATUS_ASSIGNED, Ticket::STATUS_IN_PROGRESS])) {
            return back()->with('error', 'Tiket ini tidak dapat dikembalikan.');
        }

        $this->ticketService->returnToHelpdesk($ticket, $request->reason, $user);

        return redirect()->route('admin.tickets.task-queue')
            ->with('success', 'Tiket berhasil dikembalikan ke Helpdesk.');
    }

    /**
     * List tiket yang ditugaskan ke teknisi yang login
     */
    public function assignedTickets(Request $request): Response
    {
        $tickets = $this->ticketService->getAssignedTickets(
            $request->user(),
            $request->only(['search', 'status'])
        );

        return Inertia::render('Admin/Tickets/AssignedTickets', [
            'tickets' => $tickets,
            'filters' => $request->only(['search', 'status']),
            'statuses' => [
                Ticket::STATUS_ASSIGNED => Ticket::STATUS_LABELS[Ticket::STATUS_ASSIGNED],
                Ticket::STATUS_IN_PROGRESS => Ticket::STATUS_LABELS[Ticket::STATUS_IN_PROGRESS],
                Ticket::STATUS_PENDING_USER => Ticket::STATUS_LABELS[Ticket::STATUS_PENDING_USER],
                Ticket::STATUS_PENDING_EXTERNAL => Ticket::STATUS_LABELS[Ticket::STATUS_PENDING_EXTERNAL],
                Ticket::STATUS_WAITING_APPROVAL => Ticket::STATUS_LABELS[Ticket::STATUS_WAITING_APPROVAL],
            ],
        ]);
    }

    // ==============================================
    // PHASE 3: WORK & RESOLVE
    // ==============================================

    /**
     * Set ticket to pending status
     */
    public function setPending(SetPendingRequest $request, Ticket $ticket): RedirectResponse
    {
        $user = auth()->user();

        // Check if ticket is assigned to this user
        if ($ticket->assigned_to_id !== $user->id) {
            return back()->with('error', 'Tiket ini bukan ditugaskan kepada Anda.');
        }

        // Only allow pending for in_progress tickets
        if ($ticket->status !== Ticket::STATUS_IN_PROGRESS) {
            return back()->with('error', 'Tiket harus dalam status "Dalam Proses" untuk di-pending.');
        }

        $this->ticketService->setPending(
            $ticket,
            $request->type,
            $request->reason,
            $user
        );

        $typeLabel = $request->type === 'user' ? 'User' : 'Vendor/Pihak Eksternal';
        return redirect()->route('admin.tickets.task-queue')
            ->with('success', "Tiket di-pending menunggu {$typeLabel}.");
    }

    /**
     * Resume ticket from pending status
     */
    public function resume(Request $request, Ticket $ticket): RedirectResponse
    {
        $user = auth()->user();

        // Check if user has permission to resume
        // Technician can resume any of their assigned tickets
        // Reporter can resume their own ticket if status is pending_user
        $canResume = ($ticket->assigned_to_id === $user->id) ||
            ($ticket->reporter_id === $user->id && $ticket->status === Ticket::STATUS_PENDING_USER);

        if (!$canResume) {
            return back()->with('error', 'Anda tidak memiliki akses untuk melanjutkan tiket ini.');
        }

        // Only allow resume for pending tickets
        if (!in_array($ticket->status, [Ticket::STATUS_PENDING_USER, Ticket::STATUS_PENDING_EXTERNAL])) {
            return back()->with('error', 'Tiket tidak dalam status pending.');
        }

        $this->ticketService->resumeFromPending(
            $ticket,
            $request->notes,
            $user
        );

        return back()->with('success', 'Tiket dilanjutkan kembali.');
    }

    /**
     * Request approval from Manager
     */
    public function requestApproval(RequestApprovalRequest $request, Ticket $ticket): RedirectResponse
    {
        $user = auth()->user();

        // Check if ticket is assigned to this user
        if ($ticket->assigned_to_id !== $user->id) {
            return back()->with('error', 'Tiket ini bukan ditugaskan kepada Anda.');
        }

        // Only allow request approval for in_progress tickets
        if ($ticket->status !== Ticket::STATUS_IN_PROGRESS) {
            return back()->with('error', 'Tiket harus dalam status "Dalam Proses" untuk meminta approval.');
        }

        $this->ticketService->requestApproval(
            $ticket,
            $request->validated(),
            $user
        );

        return back()->with('success', 'Permintaan approval telah dikirim ke Manager.');
    }

    /**
     * Resolve ticket with solution
     */
    public function resolve(ResolveTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $user = auth()->user();

        // Check if ticket is assigned to this user
        if ($ticket->assigned_to_id !== $user->id) {
            return back()->with('error', 'Tiket ini bukan ditugaskan kepada Anda.');
        }

        // Only allow resolve for specific statuses
        $allowedStatuses = [
            Ticket::STATUS_IN_PROGRESS,
            Ticket::STATUS_PENDING_USER,
            Ticket::STATUS_PENDING_EXTERNAL,
        ];

        if (!in_array($ticket->status, $allowedStatuses)) {
            return back()->with('error', 'Tiket tidak dapat diselesaikan dari status saat ini.');
        }

        $this->ticketService->resolveTicket(
            $ticket,
            $request->resolution,
            $user,
            $request->file('evidence', [])
        );

        return redirect()->route('admin.tickets.task-queue')
            ->with('success', 'Tiket berhasil diselesaikan. Menunggu konfirmasi dari pelapor.');
    }

    /**
     * Close ticket (Creator confirms resolution)
     */
    public function close(Request $request, Ticket $ticket): RedirectResponse
    {
        $user = auth()->user();

        // Only creator can close their own ticket
        if ($ticket->created_by_id !== $user->id) {
            return back()->with('error', 'Hanya pembuat tiket yang dapat menutup tiket ini.');
        }

        // Only allowed if status is resolved
        if ($ticket->status !== Ticket::STATUS_RESOLVED) {
            return back()->with('error', 'Tiket hanya dapat ditutup jika sudah dalam status "Selesai".');
        }

        $this->ticketService->closeTicket($ticket, $user);

        return redirect()->route('admin.tickets.task-queue')
            ->with('success', 'Tiket telah berhasil ditutup. Terima kasih atas konfirmasinya.');
    }

    /**
     * Reopen ticket (Creator not satisfied)
     */
    public function reopen(Request $request, Ticket $ticket): RedirectResponse
    {
        $user = auth()->user();

        // Only creator can reopen their own ticket
        if ($ticket->created_by_id !== $user->id) {
            return back()->with('error', 'Hanya pembuat tiket yang dapat membuka kembali tiket ini.');
        }

        // Only allowed if status is resolved
        if ($ticket->status !== Ticket::STATUS_RESOLVED) {
            return back()->with('error', 'Tiket hanya dapat dibuka kembali jika dalam status "Selesai".');
        }

        $request->validate([
            'reason' => 'required|string|min:10|max:1000'
        ]);

        $this->ticketService->reopenTicket($ticket, $request->reason, $user);

        return redirect()->route('admin.tickets.task-queue')
            ->with('success', 'Tiket telah dibuka kembali untuk diproses ulang oleh teknisi.');
    }

    /**
     * Get list of users with tickets.work permission (Teknisi)
     */
    private function getTechnicians()
    {
        return User::permission('tickets.work')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();
    }
}
