<?php

namespace Database\Seeders;

use App\Models\Submission;
use App\Models\SubmissionScreenshot;
use App\Models\User;
use App\Models\Vote;
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
        $now = now();

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
            'submission_deadline' => $now->copy()->addDays(7),
            'voting_opened_at' => null,
            'voting_closed_at' => null,
        ]);

        $votingEvent = VotingEvent::updateOrCreate([
            'slug' => 'inready-voting-2026',
        ], [
            'title' => 'Voting Karya Inready 2026',
            'description' => 'Event voting karya terkurasi dari member Inready.',
            'status' => 'voting_open',
            'submission_deadline' => $now->copy()->subDays(3),
            'voting_opened_at' => $now->copy()->subHours(6),
            'voting_closed_at' => null,
        ]);

        $closedEvent = VotingEvent::updateOrCreate([
            'slug' => 'inready-hasil-2026',
        ], [
            'title' => 'Hasil Voting Inready 2026',
            'description' => 'Event closed untuk preview hasil voting dan ranking karya.',
            'status' => 'closed',
            'submission_deadline' => $now->copy()->subDays(10),
            'voting_opened_at' => $now->copy()->subDays(5),
            'voting_closed_at' => $now->copy()->subDay(),
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

        $closedSubmissions = [
            'website_top' => Submission::updateOrCreate(
                [
                    'voting_event_id' => $closedEvent->id,
                    'submitter_id' => $members[0]->id,
                    'title' => 'Website Analytics Dashboard',
                ],
                [
                    'concentration' => 'website',
                    'description' => 'Dashboard analytics dengan visualisasi metrik performa tim.',
                    'demo_url' => 'https://example.com/analytics-dashboard',
                    'github_url' => 'https://github.com/inready/analytics-dashboard',
                    'thumbnail_path' => $placeholderImagePath,
                    'status' => 'approved',
                    'admin_notes' => 'Finalis hasil voting konsentrasi website.',
                ]
            ),
            'website_runner_up' => Submission::updateOrCreate(
                [
                    'voting_event_id' => $closedEvent->id,
                    'submitter_id' => $members[1]->id,
                    'title' => 'Website Project Tracker',
                ],
                [
                    'concentration' => 'website',
                    'description' => 'Aplikasi manajemen tugas proyek berbasis web.',
                    'demo_url' => 'https://example.com/project-tracker',
                    'github_url' => 'https://github.com/inready/project-tracker',
                    'thumbnail_path' => $placeholderImagePath,
                    'status' => 'approved',
                    'admin_notes' => 'Finalis hasil voting konsentrasi website.',
                ]
            ),
            'design_top' => Submission::updateOrCreate(
                [
                    'voting_event_id' => $closedEvent->id,
                    'submitter_id' => $members[2]->id,
                    'title' => 'Design System Kit',
                ],
                [
                    'concentration' => 'design',
                    'description' => 'Kit komponen UI untuk konsistensi desain lintas produk.',
                    'demo_url' => 'https://example.com/design-system',
                    'github_url' => 'https://github.com/inready/design-system',
                    'thumbnail_path' => $placeholderImagePath,
                    'status' => 'approved',
                    'admin_notes' => 'Finalis hasil voting konsentrasi design.',
                ]
            ),
            'mobile_top' => Submission::updateOrCreate(
                [
                    'voting_event_id' => $closedEvent->id,
                    'submitter_id' => $members[3]->id,
                    'title' => 'Mobile Attendance App',
                ],
                [
                    'concentration' => 'mobile',
                    'description' => 'Aplikasi absensi berbasis mobile dengan validasi lokasi.',
                    'demo_url' => 'https://example.com/mobile-attendance',
                    'github_url' => 'https://github.com/inready/mobile-attendance',
                    'thumbnail_path' => $placeholderImagePath,
                    'status' => 'approved',
                    'admin_notes' => 'Finalis hasil voting konsentrasi mobile.',
                ]
            ),
        ];

        foreach ($closedSubmissions as $submission) {
            SubmissionScreenshot::updateOrCreate(
                [
                    'submission_id' => $submission->id,
                    'display_order' => 0,
                ],
                [
                    'image_path' => $placeholderImagePath,
                    'caption' => 'Preview karya final',
                    'display_order' => 0,
                ]
            );
        }

        $closedVotes = [
            ['member' => $members[0], 'submission' => $closedSubmissions['website_top'], 'concentration' => 'website'],
            ['member' => $members[1], 'submission' => $closedSubmissions['website_top'], 'concentration' => 'website'],
            ['member' => $members[2], 'submission' => $closedSubmissions['website_runner_up'], 'concentration' => 'website'],
            ['member' => $members[3], 'submission' => $closedSubmissions['design_top'], 'concentration' => 'design'],
            ['member' => $members[4], 'submission' => $closedSubmissions['mobile_top'], 'concentration' => 'mobile'],
        ];

        foreach ($closedVotes as $voteData) {
            Vote::updateOrCreate(
                [
                    'voting_event_id' => $closedEvent->id,
                    'voter_id' => $voteData['member']->id,
                    'concentration' => $voteData['concentration'],
                ],
                [
                    'submission_id' => $voteData['submission']->id,
                ]
            );
        }
    }
}
