<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'requested_by_id',
        'approved_by_id',
        'request_type',
        'request_reason',
        'estimated_cost',
        'status',
        'decision_notes',
        'requested_at',
        'decided_at',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'requested_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }
}
