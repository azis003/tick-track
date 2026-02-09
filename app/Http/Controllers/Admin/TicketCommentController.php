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

        // 2. Staff (Helpdesk/Teknisi) CANNOT comment directly
        // They should use workflow actions (Return to User, etc.) which have their own notes
        $isStaff = $user->can('tickets.view-all') || $user->can('tickets.work');

        if ($isStaff) {
            return back()->with('error', 'Gunakan tombol aksi (seperti "Kembalikan ke User") untuk berkomunikasi dengan pelapor.');
        }

        // 3. Creator (who created the ticket) can only comment when ticket is returned to them
        if ($ticket->created_by_id === $user->id) {
            if ($ticket->status !== Ticket::STATUS_PENDING_USER) {
                return back()->with('error', 'Komentar hanya diperbolehkan jika tiket dikembalikan ke Anda (Pending User) untuk informasi tambahan.');
            }
        } else {
            // Not staff and not creator - no access
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

        // Auto-resume if it's pending_user and the creator is commenting
        if ($ticket->status === Ticket::STATUS_PENDING_USER && $ticket->created_by_id === $user->id) {
            $this->ticketService->resumeFromPending($ticket, 'User memberikan respon/informasi tambahan.', $user);

            // Redirect to task-queue with success message
            return redirect()->route('admin.tickets.task-queue')
                ->with('success', 'Tanggapan Anda berhasil dikirim. Tiket akan dilanjutkan oleh petugas.');
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
