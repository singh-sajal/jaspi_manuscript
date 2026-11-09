<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');

            // Who sent it: polymorphic (App\Models\Admin | App\Models\Author).
            $table->string('sender_type')->nullable()->index();
            $table->unsignedBigInteger('sender_id')->nullable()->index();

            // Who received it: polymorphic (App\Models\Admin | App\Models\Author).
            // Nullable for recipients that cannot be resolved to a model row.
            $table->string('recipient_type')->nullable()->index();
            $table->unsignedBigInteger('recipient_id')->nullable()->index();

            $table->string('to_email');
            $table->string('subject');
            $table->text('message')->nullable();

            // 'sent' or 'failed'. Sends are queued, so this records the
            // successful dispatch (or the QueuedMail/validation failure).
            $table->string('status')->default('sent');
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->index(['sender_type', 'sender_id']);
            $table->index(['recipient_type', 'recipient_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_logs');
    }
};