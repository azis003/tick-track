<?php

namespace App\Http\Controllers\Admin;

use App\Models\TicketAttachment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketAttachmentController extends Controller
{
    /**
     * Download attachment file
     */
    public function download(TicketAttachment $attachment): StreamedResponse
    {
        // Check if user can access this ticket's attachment
        $user = auth()->user();
        $ticket = $attachment->ticket;

        $canAccess = $user->can('tickets.view-all')
            || $ticket->reporter_id === $user->id
            || $ticket->created_by_id === $user->id
            || $ticket->assigned_to_id === $user->id;

        if (!$canAccess) {
            abort(403, 'Anda tidak memiliki akses ke file ini.');
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download(
            $attachment->file_path,
            $attachment->file_name
        );
    }
}
