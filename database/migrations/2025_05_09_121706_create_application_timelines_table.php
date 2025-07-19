<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('application_timelines', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('application_id')->nullable()->references('id')->on('applications')->onDelete('cascade');
            $table->string('status')->nullable();
            $table->foreignId('assigned_to_id')->nullable()->references('id')->on('admins')->onDelete('set null');
            $table->longText('remark')->nullable();
            $table->longText('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('application_timelines');
    }
};
