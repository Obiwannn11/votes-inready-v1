<?php

namespace Tests\Feature\Voting;

use App\Models\Submission;
use App\Models\User;
use App\Models\Vote;
use App\Models\VotingEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_forbids_results_page_when_event_is_still_voting_open(): void
    {
        $event = $this->createEvent([
            'slug' => 'event-masih-buka',
            'status' => 'voting_open',
        ]);

        $response = $this->get(route('voting.results', $event->slug));

        $response->assertForbidden();
        $response->assertSeeText('Akses Ditolak');
        $response->assertSeeText('Hasil belum tersedia. Voting masih berlangsung.');
    }

    public function test_shows_ranked_results_and_stats_when_event_is_closed(): void
    {
        $event = $this->createEvent([
            'slug' => 'event-hasil-closed',
            'status' => 'closed',
            'voting_closed_at' => now()->subHour(),
        ]);

        $submitter = $this->createMember(['name' => 'Submitter Demo']);

        $websiteTop = $this->createApprovedSubmission($event, $submitter, 'website', 'Website Top');
        $websiteRunnerUp = $this->createApprovedSubmission($event, $submitter, 'website', 'Website Runner Up');
        $designTop = $this->createApprovedSubmission($event, $submitter, 'design', 'Design Top');

        $this->castVotes($event, $websiteTop, 'website', 4);
        $this->castVotes($event, $websiteRunnerUp, 'website', 2);
        $this->castVotes($event, $designTop, 'design', 1);

        $response = $this->get(route('voting.results', $event->slug));

        $response->assertOk();
        $response->assertSeeText('Hasil Voting');
        $response->assertSeeText('Website');
        $response->assertSeeInOrder(['Website Top', 'Website Runner Up']);
        $response->assertSeeText('Juara #1');
        $response->assertSeeText('7');
        $response->assertSeeText('total voter');
        $response->assertSeeText('total vote');
    }

    public function test_shows_results_button_in_gallery_when_event_closed(): void
    {
        $event = $this->createEvent([
            'slug' => 'event-link-hasil',
            'status' => 'closed',
        ]);

        $submitter = $this->createMember();
        $this->createApprovedSubmission($event, $submitter, 'website', 'Submission Hasil Link');

        $response = $this->get(route('voting.gallery', $event->slug));

        $response->assertOk();
        $response->assertSeeText('Lihat Hasil Voting');
    }

    public function test_renders_custom_404_page_for_vote_routes(): void
    {
        $response = $this->get(route('voting.results', 'event-tidak-ada'));

        $response->assertNotFound();
        $response->assertSeeText('Tidak Ditemukan');
        $response->assertSeeText('Halaman yang kamu cari tidak ada.');
    }

    private function createEvent(array $overrides = []): VotingEvent
    {
        return VotingEvent::create(array_merge([
            'title' => 'Event Result Test',
            'slug' => 'event-result-test',
            'description' => 'Event untuk test hasil voting',
            'status' => 'closed',
        ], $overrides));
    }

    private function createMember(array $overrides = []): User
    {
        /** @var User $user */
        $user = User::factory()->create(array_merge([
            'role' => 'member',
            'is_active' => true,
        ], $overrides));

        return $user;
    }

    private function createApprovedSubmission(VotingEvent $event, User $submitter, string $concentration, string $title): Submission
    {
        return Submission::create([
            'voting_event_id' => $event->id,
            'submitter_id' => $submitter->id,
            'title' => $title,
            'concentration' => $concentration,
            'description' => 'Deskripsi ' . $title,
            'status' => 'approved',
            'thumbnail_path' => 'images/placeholder-ss.png',
        ]);
    }

    private function castVotes(VotingEvent $event, Submission $submission, string $concentration, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $voter = $this->createMember([
                'email' => "{$concentration}-voter-{$submission->id}-{$i}@inready.test",
            ]);

            Vote::create([
                'voting_event_id' => $event->id,
                'voter_id' => $voter->id,
                'submission_id' => $submission->id,
                'concentration' => $concentration,
            ]);
        }
    }
}
