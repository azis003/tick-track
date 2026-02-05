<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Ticket;

class Priority extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'level',
        'color',
        'description',
        'is_active',
    ];

    protected $casts = [
        'level' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Tiket yang menggunakan priority ini sebagai user priority
     */
    public function userPriorityTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_priority_id');
    }

    /**
     * Tiket yang menggunakan priority ini sebagai final priority
     */
    public function finalPriorityTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'final_priority_id');
    }
}
