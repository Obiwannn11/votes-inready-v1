<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $fillable = [
        'voting_event_id',
        'submitter_id',
        'title',
        'concentration',
        'description',
        'demo_url',
        'github_url',
        'thumbnail_path',
        'status',
        'admin_notes',
    ];

    public function event()
    {
        return $this->belongsTo(VotingEvent::class, 'voting_event_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitter_id');
    }

    public function screenshots()
    {
        return $this->hasMany(SubmissionScreenshot::class)->orderBy('display_order');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}
