<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'username',
        'name',
        'password',
        'angkatan',
        'gender',
        'has_voted',
        'password_changed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'has_voted' => 'boolean',
            'password_changed_at' => 'datetime',
        ];
    }

    /**
     * Elections yang diikuti user (DPT).
     */
    public function elections(): BelongsToMany
    {
        return $this->belongsToMany(Election::class)
            ->withPivot('has_voted', 'created_at');
    }

    /**
     * Log partisipasi voting (tanpa info kandidat).
     */
    public function voterLogs(): HasMany
    {
        return $this->hasMany(VoterLog::class);
    }

    /**
     * Audit log aksi admin.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
