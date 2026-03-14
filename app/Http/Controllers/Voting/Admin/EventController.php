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
            'status' => 'required|in:draft,submission_open,voting_open,closed',
        ]);

        // Logic check validasi status transition bisa ditambahkan di sini.

        $event->update(['status' => $data['status']]);
        return Redirect::back()->with('success', "Status diubah ke {$data['status']}.");
    }
}
