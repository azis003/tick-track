<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    // ==============================================
    // STATUS CONSTANTS
    // ==============================================
    public const STATUS_NEW = 'new';
    public const STATUS_TRIAGE = 'triage';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_PENDING_USER = 'pending_user';
    public const STATUS_PENDING_EXTERNAL = 'pending_external';
    public const STATUS_WAITING_APPROVAL = 'waiting_approval';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_REOPENED = 'reopened';

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_TRIAGE,
        self::STATUS_ASSIGNED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_PENDING_USER,
        self::STATUS_PENDING_EXTERNAL,
        self::STATUS_WAITING_APPROVAL,
        self::STATUS_RESOLVED,
        self::STATUS_CLOSED,
        self::STATUS_REOPENED,
    ];

    // Status labels dalam Bahasa Indonesia
    public const STATUS_LABELS = [
        self::STATUS_NEW => 'Baru',
        self::STATUS_TRIAGE => 'Verifikasi',
        self::STATUS_ASSIGNED => 'Ditugaskan',
        self::STATUS_IN_PROGRESS => 'Dalam Proses',
        self::STATUS_PENDING_USER => 'Pending User',
        self::STATUS_PENDING_EXTERNAL => 'Pending Eksternal',
        self::STATUS_WAITING_APPROVAL => 'Menunggu Approval',
        self::STATUS_RESOLVED => 'Selesai',
        self::STATUS_CLOSED => 'Ditutup',
        self::STATUS_REOPENED => 'Dibuka Kembali',
    ];

    // Status colors for StatusBadge component
    public const STATUS_COLORS = [
        self::STATUS_NEW => 'blue',
        self::STATUS_TRIAGE => 'purple',
        self::STATUS_ASSIGNED => 'indigo',
        self::STATUS_IN_PROGRESS => 'yellow',
        self::STATUS_PENDING_USER => 'orange',
        self::STATUS_PENDING_EXTERNAL => 'orange',
        self::STATUS_WAITING_APPROVAL => 'pink',
        self::STATUS_RESOLVED => 'green',
        self::STATUS_CLOSED => 'gray',
        self::STATUS_REOPENED => 'red',
    ];

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

    // ==============================================
    // ACCESSORS
    // ==============================================
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    // ==============================================
    // SCOPES
    // ==============================================
    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    public function scopeNeedsTriage($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to_id', $userId);
    }

}
