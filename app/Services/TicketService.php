<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\TicketAttachment;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TicketService
{
    /**
     * Generate ticket number: TKT0000001
     * Format production-ready mengikuti best practices ITSM
     */
    public function generateTicketNumber(): string
    {
        $lastTicket = Ticket::orderBy('id', 'desc')->lockForUpdate()->first();
        $nextNumber = $lastTicket ? $lastTicket->id + 1 : 1;
        return 'TKT' . str_pad($nextNumber, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Create new ticket with optional attachments
     */
    public function createTicket(array $data, User $creator, array $files = []): Ticket
    {
        return DB::transaction(function () use ($data, $creator, $files) {
            // 1. Create ticket
            $ticket = Ticket::create([
                'ticket_number' => $this->generateTicketNumber(),
                'title' => $data['title'],
                'description' => $data['description'],
                'reporter_id' => $data['reporter_id'] ?? $creator->id,
                'created_by_id' => $creator->id,
                'category_id' => $data['category_id'],
                'user_priority_id' => $data['user_priority_id'],
                'status' => Ticket::STATUS_NEW,
            ]);

            // 2. Create ticket log (action: create)
            $this->logAction($ticket, 'create', null, Ticket::STATUS_NEW, null, $creator);

            // 3. Upload attachments
            foreach ($files as $file) {
                $this->uploadAttachment($ticket, $file, $creator, 'initial');
            }

            return $ticket->load(['reporter', 'category', 'userPriority', 'attachments']);
        });
    }

    /**
     * Log ticket action (immutable audit trail)
     */
    public function logAction(
        Ticket $ticket,
        string $action,
        ?string $fromStatus,
        string $toStatus,
        ?string $notes,
        User $user
    ): TicketLog {
        return TicketLog::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }

    /**
     * Upload attachment to local storage
     */
    public function uploadAttachment(
        Ticket $ticket,
        UploadedFile $file,
        User $uploader,
        string $type = 'initial',
        ?int $commentId = null
    ): TicketAttachment {
        $path = $file->store('attachments/' . $ticket->id, 'public');

        return TicketAttachment::create([
            'ticket_id' => $ticket->id,
            'comment_id' => $commentId,
            'user_id' => $uploader->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'attachment_type' => $type,
            'created_at' => now(),
        ]);
    }

    /**
     * Get paginated tickets with filters (untuk Helpdesk/Manager)
     */
    public function getTicketsForUser(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Ticket::with(['reporter', 'category', 'userPriority', 'finalPriority', 'assignedTo']);

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('reporter', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply category filter
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Apply priority filter
        if (!empty($filters['priority_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('final_priority_id', $filters['priority_id'])
                    ->orWhere('user_priority_id', $filters['priority_id']);
            });
        }

        // Order: tickets needing verification (new, reopened) first, then by created_at desc
        return $query->orderByRaw("CASE WHEN status IN ('new', 'reopened') THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Get user's own tickets (tickets CREATED BY the user)
     * This shows tickets where created_by_id = current user
     */
    public function getMyTickets(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Ticket::with(['category', 'userPriority', 'finalPriority', 'reporter', 'assignedTo'])
            ->where('created_by_id', $user->id);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('ticket_number', 'like', "%{$filters['search']}%")
                    ->orWhere('title', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('updated_at', 'desc')
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Get task queue - tickets that require ACTION from the current user
     * This is role-aware and shows different tickets based on user's role
     */
    public function getTaskQueue(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Ticket::with(['category', 'userPriority', 'finalPriority', 'reporter', 'assignedTo', 'createdBy']);

        // Build conditions based on user role
        $conditions = [];

        // 1. Helpdesk: new/reopened tickets needing triage
        if ($user->can('tickets.triage')) {
            $conditions[] = function ($q) {
                $q->whereIn('status', [Ticket::STATUS_NEW, Ticket::STATUS_REOPENED]);
            };
        }

        // 2. Teknisi: assigned tickets needing acceptance + in_progress tickets
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

        // 3. Manager: tickets waiting approval
        if ($user->can('tickets.approve')) {
            $conditions[] = function ($q) {
                $q->where('status', Ticket::STATUS_WAITING_APPROVAL);
            };
        }

        // 4. Creator/Reporter: pending_user (need response) or resolved (need confirmation)
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

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('ticket_number', 'like', "%{$filters['search']}%")
                    ->orWhere('title', 'like', "%{$filters['search']}%");
            });
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Order by priority: tickets needing immediate action first
        return $query->orderByRaw("CASE 
                WHEN status = 'new' THEN 0
                WHEN status = 'reopened' THEN 1
                WHEN status = 'assigned' THEN 2
                WHEN status = 'pending_user' THEN 3
                WHEN status = 'resolved' THEN 4
                WHEN status = 'waiting_approval' THEN 5
                ELSE 6
            END")
            ->orderBy('updated_at', 'desc')
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Get unit tickets for Ketua Tim
     */
    public function getUnitTickets(User $ketuaTim, array $filters = []): LengthAwarePaginator
    {
        // Get tickets from users in the same unit
        $query = Ticket::with(['reporter', 'category', 'userPriority', 'finalPriority'])
            ->whereHas('reporter', fn($q) => $q->where('unit_id', $ketuaTim->unit_id));

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('ticket_number', 'like', "%{$filters['search']}%")
                    ->orWhere('title', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
    }

    // ==============================================
    // PHASE 2: TRIAGE & ASSIGNMENT METHODS
    // ==============================================

    /**
     * Get tickets that need triage (status = new or reopened)
     */
    public function getTicketsNeedTriage(array $filters = []): LengthAwarePaginator
    {
        // Include new, reopened, AND triage status (triage = returned by technician)
        $query = Ticket::with(['reporter', 'category', 'userPriority', 'finalPriority'])
            ->whereIn('status', [Ticket::STATUS_NEW, Ticket::STATUS_REOPENED, Ticket::STATUS_TRIAGE]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('reporter', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Sort: returned tickets first (those with return_reason), then by created_at
        return $query->orderByRaw('CASE WHEN return_reason IS NOT NULL THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'asc')
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Triage ticket - set final priority and change status to triage
     */
    public function triageTicket(Ticket $ticket, int $finalPriorityId, ?string $notes, User $helpdesk): Ticket
    {
        $fromStatus = $ticket->status;

        $ticket->update([
            'final_priority_id' => $finalPriorityId,
            'status' => Ticket::STATUS_TRIAGE,
        ]);

        $this->logAction($ticket, 'triage', $fromStatus, Ticket::STATUS_TRIAGE, $notes, $helpdesk);

        return $ticket->fresh(['reporter', 'category', 'userPriority', 'finalPriority']);
    }

    /**
     * Assign ticket to technician
     */
    public function assignTicket(Ticket $ticket, int $technicianId, User $assignedBy, ?string $notes = null): Ticket
    {
        $fromStatus = $ticket->status;

        $ticket->update([
            'assigned_to_id' => $technicianId,
            'assigned_by_id' => $assignedBy->id,
            'assigned_at' => now(),
            'status' => Ticket::STATUS_ASSIGNED,
        ]);

        $this->logAction($ticket, 'assign', $fromStatus, Ticket::STATUS_ASSIGNED, $notes, $assignedBy);

        return $ticket->fresh(['reporter', 'category', 'finalPriority', 'assignedTo', 'assignedBy']);
    }

    /**
     * Self-handle by Helpdesk (skip assign, go directly to in_progress)
     */
    public function startSelfHandle(Ticket $ticket, User $helpdesk): Ticket
    {
        $fromStatus = $ticket->status;

        $ticket->update([
            'assigned_to_id' => $helpdesk->id,
            'assigned_by_id' => $helpdesk->id,
            'assigned_at' => now(),
            'started_at' => now(),
            'status' => Ticket::STATUS_IN_PROGRESS,
        ]);

        $this->logAction(
            $ticket,
            'start',
            $fromStatus,
            Ticket::STATUS_IN_PROGRESS,
            'Ditangani langsung oleh Helpdesk',
            $helpdesk
        );

        return $ticket->fresh(['reporter', 'category', 'finalPriority', 'assignedTo']);
    }

    /**
     * Technician accepts and starts work on assigned ticket
     */
    public function acceptAndStart(Ticket $ticket, User $technician): Ticket
    {
        $fromStatus = $ticket->status;

        $ticket->update([
            'started_at' => now(),
            'status' => Ticket::STATUS_IN_PROGRESS,
        ]);

        $this->logAction($ticket, 'start', $fromStatus, Ticket::STATUS_IN_PROGRESS, null, $technician);

        return $ticket->fresh(['reporter', 'category', 'finalPriority', 'assignedTo']);
    }

    /**
     * Return ticket to Helpdesk for re-triage (by Technician)
     */
    public function returnToHelpdesk(Ticket $ticket, string $reason, User $technician): Ticket
    {
        $fromStatus = $ticket->status;

        $ticket->update([
            'return_reason' => $reason,
            'assigned_to_id' => null,
            'assigned_by_id' => null,
            'assigned_at' => null,
            'started_at' => null,
            'status' => Ticket::STATUS_TRIAGE,
        ]);

        $this->logAction($ticket, 'return', $fromStatus, Ticket::STATUS_TRIAGE, $reason, $technician);

        return $ticket->fresh(['reporter', 'category', 'finalPriority']);
    }

    /**
     * Get available technicians for assignment dropdown
     */
    public function getAvailableTechnicians(): \Illuminate\Database\Eloquent\Collection
    {
        return User::role('teknisi')
            ->where('is_active', true)
            ->withCount([
                'assignedTickets' => function ($q) {
                    $q->whereNotIn('status', [Ticket::STATUS_CLOSED, Ticket::STATUS_RESOLVED]);
                }
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    /**
     * Get tickets assigned to a specific technician
     */
    public function getAssignedTickets(User $technician, array $filters = []): LengthAwarePaginator
    {
        $query = Ticket::with(['reporter', 'category', 'finalPriority', 'assignedBy'])
            ->where('assigned_to_id', $technician->id)
            ->whereIn('status', [
                Ticket::STATUS_ASSIGNED,
                Ticket::STATUS_IN_PROGRESS,
                Ticket::STATUS_PENDING_USER,
                Ticket::STATUS_PENDING_EXTERNAL,
                Ticket::STATUS_WAITING_APPROVAL,
            ]);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('ticket_number', 'like', "%{$filters['search']}%")
                    ->orWhere('title', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
    }

    // ==============================================
    // PHASE 3: WORK & RESOLVE METHODS
    // ==============================================

    /**
     * Set ticket to pending status
     */
    public function setPending(Ticket $ticket, string $type, string $reason, User $user): Ticket
    {
        $fromStatus = $ticket->status;
        $newStatus = $type === 'user' ? Ticket::STATUS_PENDING_USER : Ticket::STATUS_PENDING_EXTERNAL;

        $ticket->update([
            'pending_type' => $type,
            'pending_reason' => $reason,
            'status' => $newStatus,
        ]);

        $this->logAction($ticket, 'pending', $fromStatus, $newStatus, $reason, $user);

        // Create a comment with the pending reason so it's visible in the conversation thread
        $pendingLabel = $type === 'user' ? 'Dikembalikan ke Pelapor' : 'Menunggu Vendor/Pihak Ketiga';
        \App\Models\TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'content' => "[{$pendingLabel}]\n\n{$reason}",
            'is_internal' => false,
            'created_at' => now(),
        ]);

        return $ticket->fresh(['reporter', 'category', 'finalPriority', 'assignedTo']);
    }

    /**
     * Resume from pending status
     */
    public function resumeFromPending(Ticket $ticket, ?string $notes, User $user): Ticket
    {
        $fromStatus = $ticket->status;

        $ticket->update([
            'pending_type' => null,
            'pending_reason' => null,
            'status' => Ticket::STATUS_IN_PROGRESS,
        ]);

        $this->logAction($ticket, 'resume', $fromStatus, Ticket::STATUS_IN_PROGRESS, $notes, $user);

        return $ticket->fresh(['reporter', 'category', 'finalPriority', 'assignedTo']);
    }

    /**
     * Request approval from Manager
     */
    public function requestApproval(Ticket $ticket, array $approvalData, User $requester): Ticket
    {
        $fromStatus = $ticket->status;

        // Create approval record
        \App\Models\Approval::create([
            'ticket_id' => $ticket->id,
            'requested_by_id' => $requester->id,
            'request_type' => $approvalData['request_type'],
            'request_reason' => $approvalData['request_reason'],
            'estimated_cost' => $approvalData['estimated_cost'] ?? null,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $ticket->update(['status' => Ticket::STATUS_WAITING_APPROVAL]);

        $this->logAction(
            $ticket,
            'request_approval',
            $fromStatus,
            Ticket::STATUS_WAITING_APPROVAL,
            $approvalData['request_reason'],
            $requester
        );

        return $ticket->fresh(['reporter', 'category', 'finalPriority', 'assignedTo', 'approvals']);
    }

    /**
     * Approve ticket (Manager approves the request)
     * Tiket dikembalikan ke in_progress agar teknisi melanjutkan pengerjaan
     */
    public function approveTicket(Ticket $ticket, string $decisionNotes, User $approver): Ticket
    {
        $fromStatus = $ticket->status;

        return DB::transaction(function () use ($ticket, $decisionNotes, $approver, $fromStatus) {
            // Update approval record
            $approval = $ticket->approvals()->where('status', 'pending')->latest()->first();
            if ($approval) {
                $approval->update([
                    'approved_by_id' => $approver->id,
                    'status' => 'approved',
                    'decision_notes' => $decisionNotes ?: null,
                    'decided_at' => now(),
                ]);
            }

            // Kembalikan tiket ke in_progress
            $ticket->update(['status' => Ticket::STATUS_IN_PROGRESS]);

            $this->logAction(
                $ticket,
                'approval_approved',
                $fromStatus,
                Ticket::STATUS_IN_PROGRESS,
                $decisionNotes ?: 'Permintaan disetujui oleh Manager',
                $approver
            );

            // Auto-create comment so technician can see the approval note
            if ($decisionNotes) {
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $approver->id,
                    'content' => "[Approval Disetujui]\n\n" . $decisionNotes,
                    'is_internal' => false,
                ]);
            }

            return $ticket->fresh();
        });
    }

    /**
     * Reject ticket approval (Manager rejects the request)
     * Tiket dikembalikan ke in_progress agar teknisi mencari solusi alternatif
     */
    public function rejectTicket(Ticket $ticket, string $decisionNotes, User $approver): Ticket
    {
        $fromStatus = $ticket->status;

        return DB::transaction(function () use ($ticket, $decisionNotes, $approver, $fromStatus) {
            // Update approval record
            $approval = $ticket->approvals()->where('status', 'pending')->latest()->first();
            if ($approval) {
                $approval->update([
                    'approved_by_id' => $approver->id,
                    'status' => 'rejected',
                    'decision_notes' => $decisionNotes,
                    'decided_at' => now(),
                ]);
            }

            // Kembalikan tiket ke in_progress
            $ticket->update(['status' => Ticket::STATUS_IN_PROGRESS]);

            $this->logAction(
                $ticket,
                'approval_rejected',
                $fromStatus,
                Ticket::STATUS_IN_PROGRESS,
                $decisionNotes,
                $approver
            );

            // Auto-create comment so technician can see the rejection note
            if ($decisionNotes) {
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $approver->id,
                    'content' => "[Approval Ditolak]\n\n" . $decisionNotes,
                    'is_internal' => false,
                ]);
            }

            return $ticket->fresh();
        });
    }

    /**
     * Resolve ticket with solution
     */
    public function resolveTicket(Ticket $ticket, string $resolution, User $resolver, array $evidenceFiles = []): Ticket
    {
        $fromStatus = $ticket->status;

        $ticket->update([
            'resolution' => $resolution,
            'resolved_at' => now(),
            'auto_close_at' => now()->addDays(3), // Auto-close in 3 days
            'status' => Ticket::STATUS_RESOLVED,
        ]);

        // Upload evidence files
        foreach ($evidenceFiles as $file) {
            $this->uploadAttachment($ticket, $file, $resolver, 'evidence');
        }

        $this->logAction($ticket, 'resolve', $fromStatus, Ticket::STATUS_RESOLVED, $resolution, $resolver);

        return $ticket->fresh(['reporter', 'category', 'finalPriority', 'assignedTo', 'attachments']);
    }

    /**
     * Add comment to ticket
     */
    public function addComment(Ticket $ticket, string $content, User $user, array $files = [], bool $isInternal = false): \App\Models\TicketComment
    {
        $comment = \App\Models\TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'content' => $content,
            'is_internal' => $isInternal,
        ]);

        // Upload comment attachments
        foreach ($files as $file) {
            $this->uploadAttachment($ticket, $file, $user, 'comment', $comment->id);
        }

        return $comment->load('user');
    }
    /**
     * Close ticket (user confirmation)
     */
    public function closeTicket(Ticket $ticket, User $user): Ticket
    {
        $fromStatus = $ticket->status;

        $ticket->update([
            'closed_at' => now(),
            'status' => Ticket::STATUS_CLOSED,
        ]);

        $this->logAction($ticket, 'close', $fromStatus, Ticket::STATUS_CLOSED, 'Ditutup oleh user', $user);

        return $ticket;
    }

    /**
     * Reopen ticket (user not satisfied)
     */
    public function reopenTicket(Ticket $ticket, string $reason, User $user): Ticket
    {
        $fromStatus = $ticket->status;

        $ticket->update([
            'resolution' => null,
            'resolved_at' => null,
            'auto_close_at' => null,
            'reopen_count' => $ticket->reopen_count + 1,
            'status' => Ticket::STATUS_REOPENED,
        ]);

        $this->logAction($ticket, 'reopen', $fromStatus, Ticket::STATUS_REOPENED, $reason, $user);

        return $ticket;
    }

    /**
     * Auto-close resolved tickets after 3 days
     */
    public function autoCloseExpiredTickets(): int
    {
        $tickets = Ticket::where('status', Ticket::STATUS_RESOLVED)
            ->where('auto_close_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($tickets as $ticket) {
            $ticket->update([
                'closed_at' => now(),
                'status' => Ticket::STATUS_CLOSED,
            ]);

            TicketLog::create([
                'ticket_id' => $ticket->id,
                'user_id' => null, // System action
                'action' => 'auto_close',
                'from_status' => Ticket::STATUS_RESOLVED,
                'to_status' => Ticket::STATUS_CLOSED,
                'notes' => 'Ditutup otomatis karena tidak ada respon dalam 3 hari',
            ]);

            $count++;
        }

        return $count;
    }
}


