<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Voting\Auth\LoginController;
use App\Http\Controllers\Voting\GalleryController;
use App\Http\Controllers\Voting\SubmitKaryaController;
use App\Http\Controllers\Voting\Admin\EventController as AdminEventController;
use App\Http\Controllers\Voting\Admin\SubmissionController as AdminSubmissionController;
use App\Http\Controllers\Voting\Admin\MemberController as AdminMemberController;

// Voting Public Routes
Route::get('/', [GalleryController::class, 'landing'])->name('voting.landing');

Route::get('/event/{slug}', [GalleryController::class, 'index'])->name('voting.gallery');
Route::get('/event/{slug}/karya/{id}', [GalleryController::class, 'show'])->name('voting.detail');

// Member Routes for Submission (Scenario A: Auth required)
Route::middleware('auth')->group(function () {
    Route::get('/submit/{event}', [SubmitKaryaController::class, 'form'])->name('voting.submit.form');
    Route::post('/submit/{event}', [SubmitKaryaController::class, 'store'])->name('voting.submit.store');
    Route::get('/submit/{event}/status', [SubmitKaryaController::class, 'status'])->name('voting.submit.status');
});

// Temporary Auth Routes (Fase 1 workaround)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('voting.login');
    Route::post('/login', [LoginController::class, 'login'])->name('voting.login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('voting.logout')->middleware('auth');

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'voting.admin'])->group(function () {
    // Events
    Route::resource('events', AdminEventController::class)->names([
        'index' => 'voting.admin.events.index',
        'create' => 'voting.admin.events.create',
        'store' => 'voting.admin.events.store',
        'show' => 'voting.admin.events.show',
        'edit' => 'voting.admin.events.edit',
        'update' => 'voting.admin.events.update',
        'destroy' => 'voting.admin.events.destroy',
    ]);
    Route::patch('events/{event}/status', [AdminEventController::class, 'changeStatus'])->name('voting.admin.events.changeStatus');

    // Submissions
    Route::get('events/{event}/submissions', [AdminSubmissionController::class, 'index'])->name('voting.admin.submissions');
    Route::get('submissions/{submission}', [AdminSubmissionController::class, 'show'])->name('voting.admin.submissions.show');
    Route::patch('submissions/{submission}/review', [AdminSubmissionController::class, 'review'])->name('voting.admin.submissions.review');

    // Members
    Route::resource('members', AdminMemberController::class)->names([
        'index' => 'voting.admin.members.index',
        'create' => 'voting.admin.members.create',
        'store' => 'voting.admin.members.store',
        'show' => 'voting.admin.members.show',
        'edit' => 'voting.admin.members.edit',
        'update' => 'voting.admin.members.update',
        'destroy' => 'voting.admin.members.destroy',
    ]);
});