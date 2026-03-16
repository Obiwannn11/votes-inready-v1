<?php

use App\Models\Submission;
use App\Models\User;
use App\Models\VotingEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAdminReviewEvent(array $overrides = []): VotingEvent
{
    return VotingEvent::create(array_merge([
        'title' => 'Event Review Admin',
        'slug' => 'event-review-admin',
        'description' => 'Event untuk test review admin',
        'status' => 'submission_open',
        'submission_deadline' => now()->addDay(),
    ], $overrides));
}

it('requires reject reason when admin rejects submission', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = User::factory()->create(['role' => 'member']);
    $event = createAdminReviewEvent();

    $submission = Submission::create([
        'voting_event_id' => $event->id,
        'submitter_id' => $member->id,
        'title' => 'Submission Pending',
        'concentration' => 'website',
        'description' => 'Menunggu review admin.',
        'thumbnail_path' => 'images/placeholder-ss.png',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('voting.admin.submissions.show', $submission))
        ->patch(route('voting.admin.submissions.review', $submission), [
            'status' => 'rejected',
        ]);

    $response->assertRedirect(route('voting.admin.submissions.show', $submission));
    $response->assertSessionHasErrors(['admin_notes']);

    $this->assertDatabaseHas('submissions', [
        'id' => $submission->id,
        'status' => 'pending',
        'admin_notes' => null,
    ]);
});

it('stores reject reason when admin rejects submission', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = User::factory()->create(['role' => 'member']);
    $event = createAdminReviewEvent([
        'slug' => 'event-review-admin-reject-note',
    ]);

    $submission = Submission::create([
        'voting_event_id' => $event->id,
        'submitter_id' => $member->id,
        'title' => 'Submission Untuk Reject',
        'concentration' => 'design',
        'description' => 'Perlu revisi.',
        'thumbnail_path' => 'images/placeholder-ss.png',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('voting.admin.submissions.show', $submission))
        ->patch(route('voting.admin.submissions.review', $submission), [
            'status' => 'rejected',
            'admin_notes' => 'Silakan perbaiki kontras warna dan struktur navigasi.',
        ]);

    $response->assertRedirect(route('voting.admin.submissions.show', $submission));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('submissions', [
        'id' => $submission->id,
        'status' => 'rejected',
        'admin_notes' => 'Silakan perbaiki kontras warna dan struktur navigasi.',
    ]);
});

it('clears previous reject reason when admin approves submission', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = User::factory()->create(['role' => 'member']);
    $event = createAdminReviewEvent([
        'slug' => 'event-review-admin-approve-clear',
    ]);

    $submission = Submission::create([
        'voting_event_id' => $event->id,
        'submitter_id' => $member->id,
        'title' => 'Submission Revisi',
        'concentration' => 'mobile',
        'description' => 'Sudah diperbaiki.',
        'thumbnail_path' => 'images/placeholder-ss.png',
        'status' => 'rejected',
        'admin_notes' => 'Catatan lama reject.',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('voting.admin.submissions.show', $submission))
        ->patch(route('voting.admin.submissions.review', $submission), [
            'status' => 'approved',
        ]);

    $response->assertRedirect(route('voting.admin.submissions.show', $submission));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('submissions', [
        'id' => $submission->id,
        'status' => 'approved',
        'admin_notes' => null,
    ]);
});
