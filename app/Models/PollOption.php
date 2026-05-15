<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollOption extends Model
{
    /**
     * Les champs que Laravel a le droit de remplir automatiquement.
     */
    protected $fillable = [
        'poll_id',
        'label',
    ];

    /**
     * Le sondage auquel appartient cette option.
     */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    /**
     * Les votes pour cette option.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }
}
