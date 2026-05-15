<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollVote extends Model
{
    /**
     * Les champs que Laravel a le droit de remplir automatiquement.
     */
    protected $fillable = [
        'poll_id',
        'user_id',
        'poll_option_id',
    ];

    /**
     * Le sondage auquel appartient ce vote.
     */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    /**
     * L'utilisateur qui a voté.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * L'option choisie.
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(PollOption::class, 'poll_option_id');
    }
}
