<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\Vote;
use App\Models\VotingEvent;
use Illuminate\Support\Facades\View;

class ResultController extends Controller
{
    public function index(string $slug)
    {
        $event = VotingEvent::where('slug', $slug)->firstOrFail();

        abort_unless($event->isClosed(), 403, 'Hasil belum tersedia. Voting masih berlangsung.');

        $results = Submission::where('voting_event_id', $event->id)
            ->where('status', 'approved')
            ->with('submitter')
            ->withCount('votes')
            ->get()
            ->groupBy('concentration')
            ->map(function ($group) {
                return $group->sortByDesc('votes_count')->values();
            });

        $totalVoters = Vote::where('voting_event_id', $event->id)
            ->distinct('voter_id')
            ->count('voter_id');

        $totalVotes = Vote::where('voting_event_id', $event->id)->count();

        return View::make('voting.results.index', compact('event', 'results', 'totalVoters', 'totalVotes'));
    }
}
