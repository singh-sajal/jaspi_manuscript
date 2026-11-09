<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->string('saspi_member')->nullable()->after('gender');
            $table->string('membership_id')->nullable()->after('saspi_member');
            $table->string('designation')->nullable()->after('membership_id');
            $table->string('affiliation')->nullable()->after('designation');
            $table->string('research_experience_years')->nullable()->after('affiliation');
            $table->string('avg_articles_published')->nullable()->after('research_experience_years');
            $table->string('h_index')->nullable()->after('avg_articles_published');
            $table->text('remarks')->nullable()->after('h_index');
        });
    }

    public function down()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn([
                'saspi_member',
                'membership_id',
                'designation',
                'affiliation',
                'research_experience_years',
                'avg_articles_published',
                'h_index',
                'remarks',
            ]);
        });
    }
};
