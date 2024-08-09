<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task_list_storages', function (Blueprint $table) {
            $table->boolean('is_test')->after('task_list_id')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_list_storages', function (Blueprint $table) {
            $table->dropColumn('is_test');
        });
    }
};
