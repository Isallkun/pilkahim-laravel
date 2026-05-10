<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tabel voter_logs — TIDAK ADA relasi ke Candidate.
 * Hanya menyimpan user_id + election_id + evidence_token + ip + voted_at.
 */
class VoterLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'election_id',
        'evidence_token',
        'ip_address',
        'voted_at',
    ];

    protected function casts(): array
    {
        return [
            'voted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    // TIDAK ADA relasi ke Candidate — ini kunci anonimitas
}
