<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Election extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
        'result_visibility',
        'show_countdown',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'show_countdown' => 'boolean',
        ];
    }

    /**
     * Kandidat dalam election ini.
     */
    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class)->orderBy('sort_order');
    }

    /**
     * Suara yang masuk (anonim — tanpa user_id).
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Log partisipasi pemilih (tanpa candidate_id).
     */
    public function voterLogs(): HasMany
    {
        return $this->hasMany(VoterLog::class);
    }

    /**
     * Users yang terdaftar sebagai DPT di election ini.
     */
    public function voters(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('has_voted', 'created_at');
    }

    /**
     * Cek apakah election sedang aktif.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Cek apakah hasil bisa dilihat publik.
     */
    public function isResultPublic(): bool
    {
        return $this->result_visibility === 'public';
    }
}
