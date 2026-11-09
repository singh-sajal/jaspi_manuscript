<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('invite_reviewers', function (Blueprint $table) {
            $table->unsignedTinyInteger('invitation_count')->default(0)->after('email');
            $table->timestamp('last_invited_at')->nullable()->after('invitation_count');
        });
    }

    public function down()
    {
        Schema::table('invite_reviewers', function (Blueprint $table) {
            $table->dropColumn(['invitation_count', 'last_invited_at']);
        });
    }
};
