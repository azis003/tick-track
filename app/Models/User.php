<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nip',
        'phone',
        'avatar',
        'unit_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * getPermissionArray
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getPermissionArray()
    {
        return $this->getAllPermissions()->mapWithKeys(function ($pr) {
            return [$pr['name'] => true];
        });
    }
    /**
     * Unit kerja user ini
     * @return BelongsTo<Unit, User>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Tiket yang dilaporkan user ini
     */
    public function reportedTickets()
    {
        return $this->hasMany(Ticket::class, 'reporter_id');
    }

    /**
     * Tiket yang ditugaskan ke user ini (untuk teknisi)
     */
    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to_id');
    }
}
