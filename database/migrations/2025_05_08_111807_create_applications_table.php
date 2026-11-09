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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_id')->unique();
            $table->uuid('uuid');
            $table->bigInteger('app_num')->nullable();
            $table->string('published_doi')->nullable()->comment('DOI of published manuscript');
            $table->string('submission_type')->nullable()->comment('Type of submission');
            $table->mediumText('article_type')->nullable()->comment('Type of the article being submitted');
            $table->string('title')->nullable();
            $table->string('short_title')->nullable();
            $table->string('keywords')->nullable();
            $table->string('article_type')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('author_id')->nullable()->references('id')->on('authors')->onDelete('cascade');
            $table->string('author_affiliation')->nullable()->comment('Affiliation of the corresponding author');
            $table->string('author_orcid_id')->nullable()->comment('ORCID ID of the corresponding author');
            $table->string('author_saspi_id')->nullable()->comment('SASPI ID of the corresponding author');
            $table->string('author_departent')->nullable();
            $table->string('author_organization')->nullable();
            $table->string('author_country')->nullable();
            $table->string('author_state')->nullable();
            $table->string('author_city')->nullable();
            $table->longText('co_author_data')->nullable()->comment('Name, email, phone number of co-authors');
            $table->foreignId('assigned_to_id')->nullable()->references('id')->on('admins')->onDelete('set null');
            $table->string('status')->nullable();
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
        Schema::dropIfExists('applications');
    }
};
