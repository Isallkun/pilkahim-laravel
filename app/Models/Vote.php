<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tabel votes ANONIM — TIDAK ADA relasi ke User.
 * Hanya menyimpan election_id + candidate_id + voted_at.
 */
class Vote extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'election_id',
        'candidate_id',
        'voted_at',
    ];

    protected function casts(): array
    {
        return [
            'voted_at' => 'datetime',
        ];
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    // TIDAK ADA relasi ke User — ini kunci anonimitas
}
