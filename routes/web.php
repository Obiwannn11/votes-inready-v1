<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $submissionEvents = \App\Models\VotingEvent::where('status', 'submission_open')
        ->latest()
        ->get();
    return view('voting.home', compact('submissionEvents'));
});
