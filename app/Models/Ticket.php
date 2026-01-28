<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'title',
        'description',
        'reporter_id',
        'created_by_id',
        'category_id',
        'user_priority_id',
        'final_priority_id',
        'status',
        'assigned_to_id',
        'assigned_by_id',
        'assigned_at',
        'started_at',
        'resolved_at',
        'closed_at',
        'resolution',
        'pending_reason',
        'pending_type',
        'return_reason',
        'auto_close_at',
        'reopen_count',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'auto_close_at' => 'datetime',
        'reopen_count' => 'integer',
    ];

    //RELATIONS
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function userPriority(): BelongsTo
    {
        return $this->belongsTo(Priority::class, 'user_priority_id');
    }

    public function finalPriority(): BelongsTo
    {
        return $this->belongsTo(Priority::class, 'final_priority_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TicketLog::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class);
    }

}
