<?php

namespace App\Http\Controllers\Voting\Admin;

use App\Http\Controllers\Controller;
use App\Models\VotingEvent;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\Voting\Admin\ReviewSubmissionRequest;

class SubmissionController extends Controller
{
    public function index(VotingEvent $event, Request $request)
    {
        $query = $event->submissions()->with('screenshots')->latest();

        if ($request->has('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        $submissions = $query->paginate(20);

        return View::make('voting.admin.submissions.index', compact('event', 'submissions'));
    }

    public function show(Submission $submission)
    {
        $submission->load(['event', 'screenshots', 'submitter']);
        return View::make('voting.admin.submissions.show', compact('submission'));
    }

    public function review(ReviewSubmissionRequest $request, Submission $submission)
    {
        $data = $request->validated();
        
        $submission->update(['status' => $data['status']]);

        $label = $data['status'] === 'approved' ? 'disetujui' : 'ditolak';

        return Redirect::back()->with('success', "Karya \"{$submission->title}\" berhasil {$label}.");
    }
}
