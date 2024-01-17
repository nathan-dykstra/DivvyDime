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
        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('friend');
            $table->unsignedBigInteger('group_id');
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('friend')->references('id')->on('users');
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unique(['user_id', 'friend', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balances');
    }
};
