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
     * Semua tiket dengan prioritas ini
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_priority_id');
    }
}
