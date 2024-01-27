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
        Schema::table('notification_attributes', function (Blueprint $table) {
            $table->unsignedBigInteger('expense_id')->nullable()->after('group_id');

            $table->foreign('expense_id')->references('id')->on('expenses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_attributes', function (Blueprint $table) {
            $table->dropColumn('expense_id');
        });
    }
};
