<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\SerializableClosure\UnsignedSerializableClosure;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_id')->unique();
            //$table->unsignedBigInteger('group_id')->nullable();
            //$table->unsignedBigInteger('expense_id')->nullable();
            //$table->unsignedBigInteger('payment_id')->nullable();

            $table->foreign('notification_id')->references('id')->on('notifications');
            //$table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            //$table->foreign('expense_id')->references('id')->on('expenses')->onDelete('cascade');
            //$table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_attributes');
    }
};
