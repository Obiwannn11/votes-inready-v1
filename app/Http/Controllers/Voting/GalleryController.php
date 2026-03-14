<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\Vote;
use App\Models\VotingEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

class GalleryController extends Controller
{
    public function landing()
    {
        $events = VotingEvent::whereIn('status', ['voting_open', 'closed'])
            ->latest()
            ->get();

        $activeEvent = VotingEvent::where('status', 'voting_open')->latest()->first()
            ?? VotingEvent::where('status', 'closed')->latest()->first();

        if ($activeEvent && $events->count() === 1) {
            return Redirect::route('voting.gallery', $activeEvent->slug);
        }

        return View::make('voting.landing', compact('events'));
    }

    public function index(string $slug, Request $request)
    {
        $event = VotingEvent::where('slug', $slug)->firstOrFail();

        abort_unless($event->isPublishedForGallery(), 404, 'Event belum dipublikasikan.');

        $query = $event->approvedSubmissions()->with(['screenshots', 'submitter']);

        $allowedConcentrations = ['website', 'design', 'mobile'];
        $concentration = $request->query('c');

        if ($concentration && in_array($concentration, $allowedConcentrations, true)) {
            $query->where('concentration', $concentration);
        } else {
            $concentration = null;
        }

        $submissions = $query->latest()->get();

        $userVotes = [];
        $userVoteCount = 0;

        if (Auth::check()) {
            $userVotes = Vote::where('voting_event_id', $event->id)
                ->where('voter_id', Auth::id())
                ->pluck('submission_id', 'concentration')
                ->toArray();
            $userVoteCount = count($userVotes);
        }

        $voteCounts = [];

        if ($event->isClosed()) {
            $voteCounts = Vote::where('voting_event_id', $event->id)
                ->selectRaw('submission_id, COUNT(*) as total')
                ->groupBy('submission_id')
                ->pluck('total', 'submission_id')
                ->toArray();
        }

        return View::make('voting.gallery.index', compact(
            'event',
            'submissions',
            'userVotes',
            'userVoteCount',
            'voteCounts',
            'concentration'
        ));
    }

    public function show(string $slug, int $id)
    {
        $event = VotingEvent::where('slug', $slug)->firstOrFail();

        abort_unless($event->isPublishedForGallery(), 404, 'Event belum dipublikasikan.');

        $submission = Submission::where('id', $id)
            ->where('voting_event_id', $event->id)
            ->where('status', 'approved')
            ->with(['screenshots', 'submitter'])
            ->firstOrFail();

        $userVotes = [];

        if (Auth::check()) {
            $userVotes = Vote::where('voting_event_id', $event->id)
                ->where('voter_id', Auth::id())
                ->pluck('submission_id', 'concentration')
                ->toArray();
        }

        $voteCount = $event->isClosed() ? $submission->votes()->count() : null;

        return View::make('voting.gallery.show', compact('event', 'submission', 'userVotes', 'voteCount'));
    }
}
