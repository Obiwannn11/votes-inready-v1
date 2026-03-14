<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\User;
use App\Models\Vote;
use App\Models\VotingEvent;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

class VoteController extends Controller
{
    public function store(string $slug, Submission $submission)
    {
        $event = VotingEvent::where('slug', $slug)->firstOrFail();
        $user = Auth::user();

        // Guard 1: Voting harus buka.
        if (!$event->isVotingOpen()) {
            return Redirect::back()->with('error', 'Voting belum dibuka atau sudah ditutup.');
        }

        // Guard 2: Submission harus approved dan milik event ini.
        if ($submission->voting_event_id !== $event->id || $submission->status !== 'approved') {
            abort(404);
        }

        // Guard 3: User harus aktif.
        if (!$user->is_active) {
            return Redirect::back()->with('error', 'Akun kamu tidak aktif.');
        }

        try {
            DB::transaction(function () use ($event, $submission, $user): void {
                // Lock user row untuk mengurangi race condition saat double submit.
                User::whereKey($user->id)->lockForUpdate()->first();

                $alreadyVotedConcentration = Vote::where('voting_event_id', $event->id)
                    ->where('voter_id', $user->id)
                    ->where('concentration', $submission->concentration)
                    ->exists();

                if ($alreadyVotedConcentration) {
                    throw new \DomainException('Kamu sudah vote di konsentrasi ' . $submission->concentration . '.');
                }

                $totalVotes = Vote::where('voting_event_id', $event->id)
                    ->where('voter_id', $user->id)
                    ->count();

                if ($totalVotes >= 3) {
                    throw new \DomainException('Kamu sudah menggunakan semua 3 vote.');
                }

                Vote::create([
                    'voting_event_id' => $event->id,
                    'submission_id' => $submission->id,
                    'voter_id' => $user->id,
                    'concentration' => $submission->concentration,
                ]);
            }, 3);
        } catch (\DomainException $e) {
            return Redirect::back()->with('error', $e->getMessage());
        } catch (UniqueConstraintViolationException $e) {
            return Redirect::back()->with('error', 'Vote sudah tercatat.');
        }

        return Redirect::back()->with('success', 'Vote berhasil untuk "' . $submission->title . '"!');
    }

    public function myVotes(string $slug)
    {
        $event = VotingEvent::where('slug', $slug)->firstOrFail();

        $votes = Vote::where('voting_event_id', $event->id)
            ->where('voter_id', Auth::id())
            ->with('submission')
            ->latest()
            ->get();

        return View::make('voting.vote.my-votes', compact('event', 'votes'));
    }
}