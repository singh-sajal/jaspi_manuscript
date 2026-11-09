<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inactivity_mails', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');

            $table->foreignId('application_id')
                ->constrained('applications')
                ->cascadeOnDelete();

            $table->foreignId('author_id')->nullable()
                ->constrained('authors')
                ->nullOnDelete();
            $table->string('author_email')->nullable();

            // 'bulk' = sent by the Inactive section's bulk mailer,
            // 'individual' = sent from that manuscript's row action.
            $table->string('type')->default('individual');

            $table->string('subject');
            $table->text('message')->nullable();

            $table->foreignId('sent_by')->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            // 'sent' or 'failed'. Note: mailables implement ShouldQueue, so
            // this records successful *dispatch*, not SMTP delivery.
            $table->string('status')->default('sent');
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->index(['application_id', 'type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inactivity_mails');
    }
};
