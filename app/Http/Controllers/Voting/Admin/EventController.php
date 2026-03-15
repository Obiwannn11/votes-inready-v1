<?php

namespace App\Http\Controllers\Voting\Admin;

use App\Http\Controllers\Controller;
use App\Models\VotingEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\Voting\Admin\StoreVotingEventRequest;
use App\Http\Requests\Voting\Admin\UpdateVotingEventRequest;

class EventController extends Controller
{
    public function index()
    {
        $events = VotingEvent::latest()->get();
        return View::make('voting.admin.events.index', compact('events'));
    }

    public function create()
    {
        return View::make('voting.admin.events.create');
    }

    public function store(StoreVotingEventRequest $request)
    {
        VotingEvent::create($request->validated());
        return Redirect::route('voting.admin.events.index')->with('success', 'Event berhasil dibuat.');
    }

    public function show(VotingEvent $event)
    {
        $event->load(['submissions' => function ($q) {
            $q->latest();
        }]);

        $stats = [
            'total_submissions' => $event->submissions->count(),
            'pending' => $event->submissions->where('status', 'pending')->count(),
            'approved' => $event->submissions->where('status', 'approved')->count(),
            'rejected' => $event->submissions->where('status', 'rejected')->count(),
            'total_votes' => $event->votes()->count(),
        ];

        return View::make('voting.admin.events.show', compact('event', 'stats'));
    }

    public function edit(VotingEvent $event)
    {
        return View::make('voting.admin.events.edit', compact('event'));
    }

    public function update(UpdateVotingEventRequest $request, VotingEvent $event)
    {
        $event->update($request->validated());
        return Redirect::route('voting.admin.events.index')->with('success', 'Event berhasil diupdate.');
    }

    public function destroy(VotingEvent $event)
    {
        $event->delete();
        return Redirect::route('voting.admin.events.index')->with('success', 'Event berhasil dihapus.');
    }

    public function changeStatus(Request $request, VotingEvent $event)
    {
        $data = $request->validate([
            'status' => 'required|in:draft,submission_open,voting_open,closed,archived',
        ]);

        $newStatus = $data['status'];

        if ($newStatus === $event->status) {
            return Redirect::back()->with('success', "Status event tetap {$newStatus}.");
        }

        if (!$event->canTransitionTo($newStatus)) {
            return Redirect::back()->with('error', "Transisi status tidak valid dari {$event->status} ke {$newStatus}.");
        }

        $updates = ['status' => $newStatus];

        if ($newStatus === 'voting_open' && !$event->voting_opened_at) {
            $updates['voting_opened_at'] = now();
            $updates['voting_closed_at'] = null;
        }

        if ($newStatus === 'closed' && !$event->voting_closed_at) {
            $updates['voting_closed_at'] = now();
        }

        if (in_array($newStatus, ['draft', 'submission_open'], true)) {
            $updates['voting_opened_at'] = null;
            $updates['voting_closed_at'] = null;
        }

        $event->update($updates);

        return Redirect::back()->with('success', "Status diubah ke {$newStatus}.");
    }
}
