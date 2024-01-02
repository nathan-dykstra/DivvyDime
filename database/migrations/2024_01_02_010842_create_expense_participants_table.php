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
        Schema::create('expense_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expense_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('share', 10, 2)->default(0.00);
            $table->tinyInteger('percentage')->unsigned()->nullable();
            $table->decimal('shares', 5, 1, true)->nullable();
            $table->decimal('adjustment', 10, 2)->nullable();
            $table->boolean('is_settled')->default(false);
            $table->timestamps();

            $table->foreign('expense_id')->references('id')->on('expenses');
            $table->foreign('user_id')->references('id')->on('users');

            $table->index('expense_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_participants');
    }
};
