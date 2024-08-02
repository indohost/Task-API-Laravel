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
        Schema::create('task_list_storages', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->string('filename');
            $table->string('orginal_name');
            $table->string('type');
            $table->string('path');
            $table->foreignUuid('task_list_id')->references('id')->on('task_lists');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_list_storages');
    }
};
