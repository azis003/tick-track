<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\TicketAttachment;
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

        return $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Get user's own tickets (untuk Pegawai)
     */
    public function getMyTickets(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Ticket::with(['category', 'userPriority', 'finalPriority'])
            ->where('reporter_id', $user->id);

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
}
