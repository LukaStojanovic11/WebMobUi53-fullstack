<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    /**
     * Les champs que Laravel a le droit de remplir automatiquement.
     * Sans cette liste, Poll::create() ne fonctionnerait pas (protection masse assignment).
     */
    protected $fillable = [
        'user_id',
        'question',
        'secret_token',
        'is_draft',
        'allow_multiple_choices',
        'allow_vote_change',
        'results_public',
        'duration',
        'started_at',
        'ends_at',
    ];

    /**
     * L'utilisateur propriétaire du sondage.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Les options du sondage.
     */
    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class);
    }

    /**
     * Les votes du sondage.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }
}
