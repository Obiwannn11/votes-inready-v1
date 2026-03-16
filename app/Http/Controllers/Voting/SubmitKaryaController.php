<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\SubmissionScreenshot;
use App\Http\Requests\Voting\SubmitKaryaRequest;
use App\Models\VotingEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class SubmitKaryaController extends Controller
{
    public function index()
    {
        $events = VotingEvent::where('status', 'submission_open')
            ->with([
                'submissions' => function ($query) {
                    $query->where('submitter_id', Auth::id());
                },
            ])
            ->latest()
            ->get();

        return View::make('voting.submit.index', compact('events'));
    }

    public function form(VotingEvent $event)
    {
        abort_unless($event->isSubmissionOpen(), 403, 'Submission belum dibuka atau sudah ditutup.');

        $submission = $this->getMemberSubmission($event);

        if ($submission && $submission->status !== 'rejected') {
            return Redirect::route('voting.submit.status', $event)
                ->with('info', 'Anda sudah memiliki submission aktif untuk event ini.');
        }

        $submission?->load('screenshots');

        return View::make('voting.submit.form', compact('event', 'submission'));
    }

    public function store(SubmitKaryaRequest $request, VotingEvent $event)
    {
        abort_unless($event->isSubmissionOpen(), 403, 'Submission sudah ditutup.');

        $validated = $request->validated();
        $submission = $this->getMemberSubmission($event);

        if ($submission && $submission->status !== 'rejected') {
            return Redirect::route('voting.submit.status', $event)
                ->with('error', 'Anda hanya bisa mengubah karya jika statusnya rejected.');
        }

        $isResubmission = $submission !== null;

        if (!$submission) {
            $thumbnailPath = $request->file('thumbnail')->store('voting/thumbnails', 'public');

            $submission = Submission::create([
                'voting_event_id' => $event->id,
                'submitter_id' => Auth::id(),
                'concentration' => $validated['concentration'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'thumbnail_path' => $thumbnailPath,
                'demo_url' => $validated['demo_url'] ?? null,
                'github_url' => $validated['github_url'] ?? null,
                'status' => 'pending',
                'admin_notes' => null,
            ]);
        } else {
            $updatePayload = [
                'concentration' => $validated['concentration'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'demo_url' => $validated['demo_url'] ?? null,
                'github_url' => $validated['github_url'] ?? null,
                'status' => 'pending',
                'admin_notes' => null,
            ];

            if ($request->hasFile('thumbnail')) {
                $this->deleteFileFromPublicDisk($submission->thumbnail_path);
                $updatePayload['thumbnail_path'] = $request->file('thumbnail')->store('voting/thumbnails', 'public');
            }

            $submission->update($updatePayload);
        }

        if ($request->hasFile('screenshots')) {
            $this->replaceScreenshots($submission, $request->file('screenshots'));
        }

        $message = $isResubmission
            ? 'Perbaikan karya berhasil dikirim ulang. Submission kembali menunggu review admin.'
            : 'Karya berhasil di-submit! Admin akan mereview.';

        return Redirect::route('voting.submit.status', $event)->with('success', $message);
    }

    public function status(VotingEvent $event)
    {
        $submission = $this->getMemberSubmission($event);

        return View::make('voting.submit.status', compact('event', 'submission'));
    }

    private function getMemberSubmission(VotingEvent $event): ?Submission
    {
        return Submission::query()
            ->where('voting_event_id', $event->id)
            ->where('submitter_id', Auth::id())
            ->latest()
            ->first();
    }

    private function replaceScreenshots(Submission $submission, array $screenshots): void
    {
        $submission->load('screenshots');

        foreach ($submission->screenshots as $screenshot) {
            $this->deleteFileFromPublicDisk($screenshot->image_path);
            $screenshot->delete();
        }

        foreach ($screenshots as $index => $file) {
            $path = $file->store('voting/screenshots', 'public');

            SubmissionScreenshot::create([
                'submission_id' => $submission->id,
                'image_path' => $path,
                'display_order' => $index,
            ]);
        }
    }

    private function deleteFileFromPublicDisk(?string $path): void
    {
        if (!$path || Str::startsWith($path, 'images/')) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
