<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Models\VotingEvent;
use App\Models\Submission;
use App\Models\SubmissionScreenshot;
use App\Http\Requests\Voting\SubmitKaryaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

class SubmitKaryaController extends Controller
{
    public function index()
    {
        $events = VotingEvent::where('status', 'submission_open')->latest()->get();
        return View::make('voting.submit.index', compact('events'));
    }

    public function form(VotingEvent $event)
    {
        abort_unless($event->isSubmissionOpen(), 403, 'Submission belum dibuka atau sudah ditutup.');

        return View::make('voting.submit.form', compact('event'));
    }

    public function store(SubmitKaryaRequest $request, VotingEvent $event)
    {
        abort_unless($event->isSubmissionOpen(), 403, 'Submission sudah ditutup.');

        $validated = $request->validated();

        // Simpan thumbnail
        $thumbnailPath = $request->file('thumbnail')->store('voting/thumbnails', 'public');

        // Buat submission
        $submission = Submission::create([
            'voting_event_id' => $event->id,
            'submitter_id'    => Auth::id(),
            'concentration'   => $validated['concentration'],
            'title'           => $validated['title'],
            'description'     => $validated['description'],
            'thumbnail_path'  => $thumbnailPath,
            'demo_url'        => $validated['demo_url'] ?? null,
            'github_url'      => $validated['github_url'] ?? null,
            'status'          => 'pending',
        ]);

        // Simpan screenshots
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $i => $file) {
                $path = $file->store('voting/screenshots', 'public');
                SubmissionScreenshot::create([
                    'submission_id' => $submission->id,
                    'image_path'    => $path,
                    'display_order' => $i,
                ]);
            }
        }

        return Redirect::route('voting.submit.status', $event)->with('success', 'Karya berhasil di-submit! Admin akan mereview.');
    }

    public function status(VotingEvent $event)
    {
        $submissions = Submission::where('voting_event_id', $event->id)
            ->where('submitter_id', Auth::id())
            ->latest()
            ->get();

        return View::make('voting.submit.status', compact('event', 'submissions'));
    }
}
