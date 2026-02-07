<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Services\TicketService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class TicketCommentController extends Controller implements HasMiddleware
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
            new Middleware('permission:comments.create', only: ['store']),
        ];
    }

    /**
     * Store a new comment
     */
    public function store(StoreCommentRequest $request, Ticket $ticket): RedirectResponse
    {
        // 1. Check if ticket is closed or resolved
        if (in_array($ticket->status, [Ticket::STATUS_CLOSED, Ticket::STATUS_RESOLVED])) {
            $message = $ticket->status === Ticket::STATUS_CLOSED ? 'Tiket sudah ditutup.' : 'Tiket sudah selesai.';
            return back()->with('error', $message . ' Tidak dapat menambahkan komentar lagi.');
        }

        $user = $request->user();

        // 2. Define "dikembalikan" or "tanya yang belum jelas" policy for reporter (Pegawai)
        // If user is the reporter and doesn't have 'view-all' (staff) permission
        $isStaff = $user->can('tickets.view-all') || $user->can('tickets.work');

        if (!$isStaff && $ticket->reporter_id === $user->id) {
            $allowedReporterStatuses = [
                Ticket::STATUS_NEW,
                Ticket::STATUS_PENDING_USER,
                Ticket::STATUS_REOPENED
            ];

            if (!in_array($ticket->status, $allowedReporterStatuses)) {
                return back()->with('error', 'Komentar hanya diperbolehkan saat tiket dikembalikan (Pending User) atau saat baru dibuat.');
            }
        }

        // 3. General Access Check
        $canComment = $isStaff
            || $ticket->reporter_id === $user->id
            || $ticket->created_by_id === $user->id;

        if (!$canComment) {
            return back()->with('error', 'Anda tidak memiliki akses untuk berkomentar pada tiket ini.');
        }

        $validated = $request->validated();

        $this->ticketService->addComment(
            $ticket,
            $validated['content'],
            $user,
            $request->file('attachments', []),
            $validated['is_internal'] ?? false
        );

        // Auto-resume if it's pending_user and the reporter is commenting
        if ($ticket->status === Ticket::STATUS_PENDING_USER && $ticket->reporter_id === $user->id) {
            $this->ticketService->resumeFromPending($ticket, 'User memberikan respon/informasi tambahan.', $user);
        }

        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }

    /**
     * Update a comment (owner only)
     */
    public function update(Request $request, TicketComment $comment): RedirectResponse
    {
        $ticket = $comment->ticket;
        if (in_array($ticket->status, [Ticket::STATUS_CLOSED, Ticket::STATUS_RESOLVED])) {
            return back()->with('error', 'Tiket sudah selesai/ditutup. Tidak dapat mengedit komentar.');
        }

        // Only owner can edit
        if ($comment->user_id !== auth()->id()) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengedit komentar ini.');
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:3', 'max:5000'],
        ]);

        $comment->update([
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Komentar berhasil diperbarui.');
    }

    /**
     * Delete a comment (owner only)
     */
    public function destroy(TicketComment $comment): RedirectResponse
    {
        $ticket = $comment->ticket;
        if (in_array($ticket->status, [Ticket::STATUS_CLOSED, Ticket::STATUS_RESOLVED])) {
            return back()->with('error', 'Tiket sudah selesai/ditutup. Tidak dapat menghapus komentar.');
        }

        // Only owner can delete
        if ($comment->user_id !== auth()->id()) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menghapus komentar ini.');
        }

        // Delete attachments associated with this comment
        foreach ($comment->attachments as $attachment) {
            \Storage::disk('public')->delete($attachment->file_path);
            $attachment->delete();
        }

        $comment->delete();

        return back()->with('success', 'Komentar berhasil dihapus.');
    }
}
