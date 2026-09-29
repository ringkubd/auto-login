<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add an index on app_token for fast token lookups.
     *
     * @return void
     */
    public function up()
    {
        $tableName = config('autologin.users_table', 'users');

        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'app_token')) {
            return;
        }

        try {
            Schema::table($tableName, function (Blueprint $table) {
                $table->index('app_token', 'auto_login_app_token_index');
            });
        } catch (\Throwable $e) {
            // Index already exists or the platform does not support it; ignore.
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableName = config('autologin.users_table', 'users');

        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'app_token')) {
            return;
        }

        try {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropIndex('auto_login_app_token_index');
            });
        } catch (\Throwable $e) {
            // Ignore.
        }
    }
};
