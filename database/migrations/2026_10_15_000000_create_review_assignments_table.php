<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-reviewer review deadlines.
 *
 * One row represents a single reviewer assignment cycle for a manuscript
 * slot (internal or external). The deadline is cumulative from the day the
 * reviewer was assigned (NOT from the day they accepted):
 *
 *   first assignment for a slot : reminder #1 day 14, reminder #2 day 21, timeout day 24
 *   every replacement reviewer  : reminder #1 day  7, reminder #2 day 10, timeout day 12
 *
 * The day offsets are hard-coded in App\Models\ReviewAssignment::PROFILE_DAYS.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('review_assignments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();

            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();

            // The assignment timeline row that opened this cycle. Doubles as the
            // de-dupe key so re-running the sync never creates a second row.
            $table->foreignId('timeline_id')->nullable()->constrained('application_timelines')->nullOnDelete();

            $table->string('type')->default('internal'); // internal | external

            // 1 = first reviewer for this slot (long window), 2+ = replacement (short window).
            $table->unsignedSmallInteger('cycle')->default(1);
            $table->string('profile')->default('long'); // long | short

            $table->timestamp('assigned_at')->nullable();

            // Precomputed, cumulative deadlines derived from assigned_at + profile.
            $table->timestamp('reminder_1_at')->nullable();
            $table->timestamp('reminder_2_at')->nullable();
            $table->timestamp('timeout_at')->nullable();

            $table->timestamp('reminder_1_sent_at')->nullable();
            $table->timestamp('reminder_2_sent_at')->nullable();
            $table->timestamp('timeout_notified_at')->nullable();

            $table->string('status')->default('active'); // active | submitted | declined | closed

            $table->timestamps();

            $table->index(['application_id', 'type']);
            $table->index(['status', 'reminder_1_at']);
            $table->index(['status', 'timeout_at']);
            $table->unique('timeline_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('review_assignments');
    }
};
