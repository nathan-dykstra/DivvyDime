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
        Schema::create('group_invites', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->unsignedBigInteger('group_id');
            $table->string('token', 20)->unique();
            $table->timestamps();

            $table->foreign('group_id')->references('id')->on('groups');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_invites');
    }
};
