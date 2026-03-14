<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voting_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submitter_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('concentration');
            $table->text('description');
            $table->string('demo_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};