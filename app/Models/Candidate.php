<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'election_id',
        'name',
        'photo_path',
        'visi',
        'misi',
        'video_url',
        'sort_order',
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    /**
     * Suara yang diterima kandidat ini.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
