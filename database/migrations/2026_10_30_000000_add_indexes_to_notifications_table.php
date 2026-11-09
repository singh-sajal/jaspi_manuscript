<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->index('category', 'notifications_category_index');
            $table->index('read_at', 'notifications_read_at_index');
            $table->index('created_at', 'notifications_created_at_index');
        });
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_category_index');
            $table->dropIndex('notifications_read_at_index');
            $table->dropIndex('notifications_created_at_index');
        });
    }
};
