<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = [
        'voting_event_id',
        'voter_id',
        'submission_id',
        'concentration',
    ];

    public function event()
    {
        return $this->belongsTo(VotingEvent::class, 'voting_event_id');
    }

    public function voter()
    {
        return $this->belongsTo(User::class, 'voter_id');
    }

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }
}
