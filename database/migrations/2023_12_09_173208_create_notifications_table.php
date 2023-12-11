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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_type_id');
            $table->unsignedBigInteger('creator');
            $table->unsignedBigInteger('sender');
            $table->unsignedBigInteger('recipient');
            $table->timestamps();

            $table->foreign('notification_type_id')->references('id')->on('notification_types');
            $table->foreign('creator')->references('id')->on('users');
            $table->foreign('sender')->references('id')->on('users');
            $table->foreign('recipient')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
