<?php

namespace Tests\Feature\Voting;

use App\Models\Submission;
use App\Models\User;
use App\Models\Vote;
use App\Models\VotingEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class VoteMechanismTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirects_guest_vote_attempt_to_login(): void
    {
        $event = $this->createVotingEvent();
        $submitter = $this->createActiveMember();
        $submission = $this->createApprovedSubmission($event, $submitter, 'website', 'Karya Website 1');

        $response = $this->post(route('voting.vote', [$event->slug, $submission->id]));

        $response->assertRedirect(route('voting.login'));
    }

    public function test_blocks_login_for_inactive_user(): void
    {
        $user = User::factory()->create([
            'role' => 'member',
            'is_active' => false,
            'email' => 'inactive-member@inready.com',
        ]);

        $response = $this->from(route('voting.login'))->post(route('voting.login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('voting.login'));
        $response->assertSessionHas('error', 'Akun tidak aktif. Hubungi admin.');
        $this->assertGuest();
    }

    public function test_stores_vote_successfully_for_eligible_user(): void
    {
        $voter = $this->createActiveMember();
        $submitter = $this->createActiveMember();
        $event = $this->createVotingEvent();
        $submission = $this->createApprovedSubmission($event, $submitter, 'website', 'Karya Website 2');

        $response = $this->actingAs($voter)
            ->from(route('voting.detail', [$event->slug, $submission->id]))
            ->post(route('voting.vote', [$event->slug, $submission->id]));

        $response->assertRedirect(route('voting.detail', [$event->slug, $submission->id]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('votes', [
            'voting_event_id' => $event->id,
            'voter_id' => $voter->id,
            'submission_id' => $submission->id,
            'concentration' => 'website',
        ]);
    }

    public function test_rejects_second_vote_in_the_same_concentration(): void
    {
        $voter = $this->createActiveMember();
        $firstSubmitter = $this->createActiveMember();
        $secondSubmitter = $this->createActiveMember();
        $event = $this->createVotingEvent();

        $firstSubmission = $this->createApprovedSubmission($event, $firstSubmitter, 'website', 'Karya Website A');
        $secondSubmission = $this->createApprovedSubmission($event, $secondSubmitter, 'website', 'Karya Website B');

        $this->actingAs($voter)->post(route('voting.vote', [$event->slug, $firstSubmission->id]));

        $response = $this->actingAs($voter)
            ->from(route('voting.detail', [$event->slug, $secondSubmission->id]))
            ->post(route('voting.vote', [$event->slug, $secondSubmission->id]));

        $response->assertRedirect(route('voting.detail', [$event->slug, $secondSubmission->id]));
        $response->assertSessionHas('error', 'Kamu sudah vote di konsentrasi website.');
        $this->assertDatabaseCount('votes', 1);
    }

    public function test_rejects_voting_when_total_vote_has_reached_three(): void
    {
        $voter = $this->createActiveMember();
        $websiteSubmitter = $this->createActiveMember();
        $designSubmitter = $this->createActiveMember();
        $mobileSubmitter = $this->createActiveMember();
        $extraSubmitter = $this->createActiveMember();
        $event = $this->createVotingEvent();

        $websiteSubmission = $this->createApprovedSubmission($event, $websiteSubmitter, 'website', 'Karya Website Max');
        $designSubmission = $this->createApprovedSubmission($event, $designSubmitter, 'design', 'Karya Design Max');
        $mobileSubmission = $this->createApprovedSubmission($event, $mobileSubmitter, 'mobile', 'Karya Mobile Max');
        $extraSubmission = $this->createApprovedSubmission($event, $extraSubmitter, 'game', 'Karya Game Extra');

        Vote::create([
            'voting_event_id' => $event->id,
            'voter_id' => $voter->id,
            'submission_id' => $websiteSubmission->id,
            'concentration' => 'website',
        ]);

        Vote::create([
            'voting_event_id' => $event->id,
            'voter_id' => $voter->id,
            'submission_id' => $designSubmission->id,
            'concentration' => 'design',
        ]);

        Vote::create([
            'voting_event_id' => $event->id,
            'voter_id' => $voter->id,
            'submission_id' => $mobileSubmission->id,
            'concentration' => 'mobile',
        ]);

        $response = $this->actingAs($voter)
            ->from(route('voting.detail', [$event->slug, $extraSubmission->id]))
            ->post(route('voting.vote', [$event->slug, $extraSubmission->id]));

        $response->assertRedirect(route('voting.detail', [$event->slug, $extraSubmission->id]));
        $response->assertSessionHas('error', 'Kamu sudah menggunakan semua 3 vote.');
        $this->assertDatabaseCount('votes', 3);
    }

    public function test_returns_not_found_for_submission_from_another_event(): void
    {
        $voter = $this->createActiveMember();
        $submitter = $this->createActiveMember();

        $eventA = $this->createVotingEvent([
            'slug' => 'event-a',
            'title' => 'Event A',
        ]);

        $eventB = $this->createVotingEvent([
            'slug' => 'event-b',
            'title' => 'Event B',
        ]);

        $submissionInEventB = $this->createApprovedSubmission($eventB, $submitter, 'design', 'Submission Event B');

        $response = $this->actingAs($voter)
            ->post(route('voting.vote', [$eventA->slug, $submissionInEventB->id]));

        $response->assertNotFound();
    }

    public function test_returns_not_found_for_unapproved_submission(): void
    {
        $voter = $this->createActiveMember();
        $submitter = $this->createActiveMember();
        $event = $this->createVotingEvent([
            'slug' => 'event-unapproved',
            'title' => 'Event Unapproved',
        ]);

        $submission = Submission::create([
            'voting_event_id' => $event->id,
            'submitter_id' => $submitter->id,
            'title' => 'Submission Pending',
            'concentration' => 'website',
            'description' => 'Belum approved',
            'status' => 'pending',
            'thumbnail_path' => 'images/placeholder-ss.png',
        ]);

        $response = $this->actingAs($voter)
            ->post(route('voting.vote', [$event->slug, $submission->id]));

        $response->assertNotFound();
    }

    public function test_shows_only_current_user_votes_in_my_votes_page(): void
    {
        $voter = $this->createActiveMember();
        $otherVoter = $this->createActiveMember();
        $mySubmissionSubmitter = $this->createActiveMember();
        $otherSubmissionSubmitter = $this->createActiveMember();
        $otherEventSubmissionSubmitter = $this->createActiveMember();

        $event = $this->createVotingEvent([
            'slug' => 'event-my-votes',
            'title' => 'Event My Votes',
        ]);

        $otherEvent = $this->createVotingEvent([
            'slug' => 'event-other',
            'title' => 'Event Other',
        ]);

        $mySubmission = $this->createApprovedSubmission($event, $mySubmissionSubmitter, 'website', 'Karya Saya di Event');
        $otherSubmissionSameEvent = $this->createApprovedSubmission($event, $otherSubmissionSubmitter, 'design', 'Karya Orang Lain di Event');
        $mySubmissionOtherEvent = $this->createApprovedSubmission($otherEvent, $otherEventSubmissionSubmitter, 'mobile', 'Karya Saya di Event Lain');

        Vote::create([
            'voting_event_id' => $event->id,
            'voter_id' => $voter->id,
            'submission_id' => $mySubmission->id,
            'concentration' => 'website',
        ]);

        Vote::create([
            'voting_event_id' => $event->id,
            'voter_id' => $otherVoter->id,
            'submission_id' => $otherSubmissionSameEvent->id,
            'concentration' => 'design',
        ]);

        Vote::create([
            'voting_event_id' => $otherEvent->id,
            'voter_id' => $voter->id,
            'submission_id' => $mySubmissionOtherEvent->id,
            'concentration' => 'mobile',
        ]);

        $response = $this->actingAs($voter)->get(route('voting.my-votes', $event->slug));

        $response->assertOk();
        $response->assertSeeText('Karya Saya di Event');
        $response->assertDontSeeText('Karya Orang Lain di Event');
        $response->assertDontSeeText('Karya Saya di Event Lain');
    }

    public function test_enables_vote_button_for_eligible_member_on_detail_page(): void
    {
        $voter = $this->createActiveMember();
        $submitter = $this->createActiveMember();
        $event = $this->createVotingEvent([
            'slug' => 'event-detail-eligible',
        ]);

        $submission = $this->createApprovedSubmission($event, $submitter, 'website', 'Karya Detail Eligible');

        $response = $this->actingAs($voter)->get(route('voting.detail', [$event->slug, $submission->id]));

        $response->assertOk();
        $response->assertSeeText('Vote Karya Ini');
    }

    public function test_hides_vote_button_when_user_already_voted_in_same_concentration(): void
    {
        $voter = $this->createActiveMember();
        $firstSubmitter = $this->createActiveMember();
        $secondSubmitter = $this->createActiveMember();
        $event = $this->createVotingEvent([
            'slug' => 'event-detail-locked',
        ]);

        $alreadyVotedSubmission = $this->createApprovedSubmission($event, $firstSubmitter, 'website', 'Karya Sudah Divote');
        $otherSubmissionSameConcentration = $this->createApprovedSubmission($event, $secondSubmitter, 'website', 'Karya Terkunci');

        Vote::create([
            'voting_event_id' => $event->id,
            'voter_id' => $voter->id,
            'submission_id' => $alreadyVotedSubmission->id,
            'concentration' => 'website',
        ]);

        $response = $this->actingAs($voter)->get(route('voting.detail', [$event->slug, $otherSubmissionSameConcentration->id]));

        $response->assertOk();
        $response->assertSeeText('Sudah vote konsentrasi website');
        $response->assertDontSeeText('Vote Karya Ini');
    }

    public function test_keeps_vote_route_protected_by_throttle_middleware(): void
    {
        $route = Route::getRoutes()->getByName('voting.vote');

        $this->assertNotNull($route);
        $this->assertContains('throttle:30,1', $route->gatherMiddleware());
    }

    private function createVotingEvent(array $overrides = []): VotingEvent
    {
        return VotingEvent::create(array_merge([
            'title' => 'Event Voting Test',
            'slug' => 'event-voting-test',
            'description' => 'Event untuk validasi mekanisme voting',
            'status' => 'voting_open',
        ], $overrides));
    }

    private function createActiveMember(array $overrides = []): User
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
            'description' => 'Deskripsi karya ' . $title,
            'status' => 'approved',
            'thumbnail_path' => 'images/placeholder-ss.png',
        ]);
    }
}