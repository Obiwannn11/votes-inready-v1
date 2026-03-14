<?php

namespace Database\Seeders;

use App\Models\Submission;
use App\Models\SubmissionScreenshot;
use App\Models\User;
use App\Models\VotingEvent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VotingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $placeholderImagePath = 'images/placeholder-ss.png';

        User::updateOrCreate(
            ['email' => 'admin@inready.com'],
            [
                'name' => 'Admin Inready',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $members = [];
        for ($i = 1; $i <= 5; $i++) {
            $member = User::updateOrCreate(
                ['email' => "member{$i}@inready.com"],
                [
                    'name' => "Member {$i}",
                    'password' => Hash::make('password'),
                    'role' => 'member',
                    'is_active' => true,
                ]
            );
            $members[] = $member;
        }

        $submissionEvent = VotingEvent::updateOrCreate([
            'slug' => 'inready-showcase-2026',
        ], [
            'title' => 'Pameran Karya Inready 2026',
            'description' => 'Ajang pameran hasil karya dan evaluasi anggota Inready Workgroup.',
            'status' => 'submission_open',
            'submission_deadline' => now()->addDays(7),
        ]);

        $votingEvent = VotingEvent::updateOrCreate([
            'slug' => 'inready-voting-2026',
        ], [
            'title' => 'Voting Karya Inready 2026',
            'description' => 'Event voting karya terkurasi dari member Inready.',
            'status' => 'voting_open',
            'submission_deadline' => now()->subDays(3),
        ]);

        $concentrations = ['website', 'design', 'mobile'];

        foreach ($members as $index => $member) {
            $concentration = $concentrations[$index % count($concentrations)];

            $draftSubmission = Submission::updateOrCreate(
                [
                    'voting_event_id' => $submissionEvent->id,
                    'submitter_id' => $member->id,
                    'title' => "Draft {$concentration} oleh {$member->name}",
                ],
                [
                    'concentration' => $concentration,
                    'description' => "Submission awal {$concentration} oleh {$member->name} untuk proses review admin.",
                    'demo_url' => 'https://example.com/demo',
                    'github_url' => 'https://github.com/inready/example-submission',
                    'thumbnail_path' => $placeholderImagePath,
                    'status' => $index === 0 ? 'rejected' : 'pending',
                    'admin_notes' => $index === 0 ? 'Perbaiki dokumentasi fitur utama sebelum submit ulang.' : null,
                ]
            );

            SubmissionScreenshot::updateOrCreate(
                [
                    'submission_id' => $draftSubmission->id,
                    'display_order' => 0,
                ],
                [
                    'image_path' => $placeholderImagePath,
                    'caption' => 'Placeholder screenshot',
                    'display_order' => 0,
                ]
            );

            if ($index < 3) {
                $finalSubmission = Submission::updateOrCreate(
                    [
                        'voting_event_id' => $votingEvent->id,
                        'submitter_id' => $member->id,
                        'title' => "Karya Final {$concentration} {$member->name}",
                    ],
                    [
                        'concentration' => $concentration,
                        'description' => "Karya final {$concentration} dari {$member->name} yang siap mengikuti voting.",
                        'demo_url' => 'https://example.com/final-demo',
                        'github_url' => 'https://github.com/inready/final-project',
                        'thumbnail_path' => $placeholderImagePath,
                        'status' => 'approved',
                        'admin_notes' => 'Karya lolos kurasi dan masuk tahap voting.',
                    ]
                );

                SubmissionScreenshot::updateOrCreate(
                    [
                        'submission_id' => $finalSubmission->id,
                        'display_order' => 0,
                    ],
                    [
                        'image_path' => $placeholderImagePath,
                        'caption' => 'Preview utama karya',
                        'display_order' => 0,
                    ]
                );

                SubmissionScreenshot::updateOrCreate(
                    [
                        'submission_id' => $finalSubmission->id,
                        'display_order' => 1,
                    ],
                    [
                        'image_path' => $placeholderImagePath,
                        'caption' => 'Preview tambahan karya',
                        'display_order' => 1,
                    ]
                );
            }
        }
    }
}
