<?php

use App\Models\Submission;
use App\Models\SubmissionScreenshot;
use App\Models\User;
use App\Models\VotingEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createSubmissionEvent(array $overrides = []): VotingEvent
{
    return VotingEvent::create(array_merge([
        'title' => 'Event Submit Karya',
        'slug' => 'event-submit-karya',
        'description' => 'Event untuk test submit karya',
        'status' => 'submission_open',
        'submission_deadline' => now()->addDay(),
    ], $overrides));
}

it('redirects guest from submit form to login', function () {
    $event = createSubmissionEvent();

    $response = $this->get(route('voting.submit.form', $event));

    $response->assertRedirect(route('voting.login'));
});

it('allows member to access submit form when submission is open', function () {
    $member = User::factory()->create(['role' => 'member']);
    $event = createSubmissionEvent();

    $response = $this->actingAs($member)->get(route('voting.submit.form', $event));

    $response->assertOk();
    $response->assertViewIs('voting.submit.form');
});

it('forbids form access when event status is not submission_open', function () {
    $member = User::factory()->create(['role' => 'member']);
    $event = createSubmissionEvent([
        'status' => 'draft',
        'slug' => 'event-draft',
    ]);

    $response = $this->actingAs($member)->get(route('voting.submit.form', $event));

    $response->assertForbidden();
});

it('forbids submit when submission deadline has passed', function () {
    Storage::fake('public');

    $member = User::factory()->create(['role' => 'member']);
    $event = createSubmissionEvent([
        'slug' => 'event-deadline-lewat',
        'submission_deadline' => now()->subMinute(),
    ]);

    $payload = [
        'concentration' => 'website',
        'title' => 'Submission telat',
        'description' => 'Konten deskripsi submission',
        'thumbnail' => UploadedFile::fake()->image('thumb.jpg'),
    ];

    $response = $this->actingAs($member)->post(route('voting.submit.store', $event), $payload);

    $response->assertForbidden();
    $this->assertDatabaseCount('submissions', 0);
});

it('rejects submit for admin role', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);
    $event = createSubmissionEvent([
        'slug' => 'event-admin-submit',
    ]);

    $payload = [
        'concentration' => 'design',
        'title' => 'Karya admin',
        'description' => 'Admin tidak boleh submit dari flow member.',
        'thumbnail' => UploadedFile::fake()->image('thumb.jpg'),
    ];

    $response = $this->actingAs($admin)->post(route('voting.submit.store', $event), $payload);

    $response->assertForbidden();
    $this->assertDatabaseCount('submissions', 0);
});

it('validates required thumbnail on submit', function () {
    $member = User::factory()->create(['role' => 'member']);
    $event = createSubmissionEvent([
        'slug' => 'event-validasi-thumbnail',
    ]);

    $payload = [
        'concentration' => 'mobile',
        'title' => 'Karya tanpa thumbnail',
        'description' => 'Harus gagal karena tidak upload thumbnail.',
    ];

    $response = $this->actingAs($member)->from(route('voting.submit.form', $event))
        ->post(route('voting.submit.store', $event), $payload);

    $response->assertRedirect(route('voting.submit.form', $event));
    $response->assertSessionHasErrors(['thumbnail']);
    $this->assertDatabaseCount('submissions', 0);
});

it('stores submission and screenshots for member', function () {
    Storage::fake('public');

    $member = User::factory()->create(['role' => 'member']);
    $event = createSubmissionEvent([
        'slug' => 'event-submit-sukses',
    ]);

    $payload = [
        'concentration' => 'website',
        'title' => 'Aplikasi Inventori',
        'description' => 'Aplikasi inventori berbasis web dengan fitur pelacakan stok.',
        'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg'),
        'screenshots' => [
            UploadedFile::fake()->image('screen-1.jpg'),
            UploadedFile::fake()->image('screen-2.jpg'),
        ],
        'demo_url' => 'https://example.com/demo',
        'github_url' => 'https://github.com/example/repo',
    ];

    $response = $this->actingAs($member)->post(route('voting.submit.store', $event), $payload);

    $response->assertRedirect(route('voting.submit.status', $event));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('submissions', [
        'voting_event_id' => $event->id,
        'submitter_id' => $member->id,
        'title' => 'Aplikasi Inventori',
        'concentration' => 'website',
        'status' => 'pending',
    ]);

    $submission = Submission::where('title', 'Aplikasi Inventori')->firstOrFail();

    expect($submission->thumbnail_path)->not->toBeNull();
    Storage::disk('public')->assertExists($submission->thumbnail_path);

    $screenshots = SubmissionScreenshot::where('submission_id', $submission->id)->get();
    expect($screenshots)->toHaveCount(2);

    foreach ($screenshots as $screenshot) {
        Storage::disk('public')->assertExists($screenshot->image_path);
    }
});

it('shows only current member submissions on status page', function () {
    $event = createSubmissionEvent([
        'slug' => 'event-status-page',
    ]);

    $owner = User::factory()->create(['role' => 'member']);
    $otherMember = User::factory()->create(['role' => 'member']);

    Submission::create([
        'voting_event_id' => $event->id,
        'submitter_id' => $owner->id,
        'title' => 'Karya Saya',
        'concentration' => 'design',
        'description' => 'Deskripsi karya saya',
        'status' => 'pending',
    ]);

    Submission::create([
        'voting_event_id' => $event->id,
        'submitter_id' => $otherMember->id,
        'title' => 'Karya Orang Lain',
        'concentration' => 'mobile',
        'description' => 'Deskripsi karya orang lain',
        'status' => 'approved',
    ]);

    $response = $this->actingAs($owner)->get(route('voting.submit.status', $event));

    $response->assertOk();
    $response->assertSeeText('Karya Saya');
    $response->assertDontSeeText('Karya Orang Lain');
});