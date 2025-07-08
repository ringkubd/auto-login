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
        $tableName = config('autologin.users_table', 'users');
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'app_token')) {
                $table->string('app_token')->nullable()->after('remember_token')->comment('Auto-login app token');
            }
            if (!Schema::hasColumn($tableName, 'app_reference')) {
                $table->string('app_reference')->nullable()->after('app_token')->comment('Auto-login app reference');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableName = config('autologin.users_table', 'users');
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (Schema::hasColumn($tableName, 'app_token')) {
                $table->dropColumn('app_token');
            }
            if (Schema::hasColumn($tableName, 'app_reference')) {
                $table->dropColumn('app_reference');
            }
        });
    }
};
