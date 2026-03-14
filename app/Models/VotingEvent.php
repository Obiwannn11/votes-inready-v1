<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VotingEvent extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'status',
        'submission_deadline',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = \Illuminate\Support\Str::slug($event->title) . '-' . uniqid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'submission_deadline' => 'datetime',
        ];
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function approvedSubmissions()
    {
        return $this->submissions()->where('status', 'approved');
    }

    public function isSubmissionOpen(): bool
    {
        if ($this->status !== 'submission_open') {
            return false;
        }

        if ($this->submission_deadline && now()->greaterThan($this->submission_deadline)) {
            return false;
        }

        return true;
    }

    public function isVotingOpen(): bool
    {
        return $this->status === 'voting_open';
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['closed', 'archived'], true);
    }

    public function isPublishedForGallery(): bool
    {
        return in_array($this->status, ['submission_open', 'voting_open', 'closed', 'archived'], true);
    }
}
