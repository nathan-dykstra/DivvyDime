<?php

use App\Models\NotificationType;
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
        Schema::create('notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100);
        });

        NotificationType::insert([
            ['type' => 'Expense'],
            ['type' => 'Reimbursement'],
            ['type' => 'Reminder'],
            ['type' => 'Payment'],
            ['type' => 'Payment Confirmed'],
            ['type' => 'Balance Settled'],
            ['type' => 'Friend Request'],
            ['type' => 'Friend Request Accepted'],
            ['type' => 'Invited to Group'],
            ['type' => 'Joined Group'],
            ['type' => 'Left Group'],
            ['type' => 'Removed from Group'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_types');
    }
};
