<?php

use App\Models\ExpenseType;
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
        Schema::create('expense_types', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100);
        });

        ExpenseType::insert([
            ['type' => 'Equal'],
            ['type' => 'Amount'],
            ['type' => 'Percentage'],
            ['type' => 'Share'],
            ['type' => 'Adjustment'],
            ['type' => 'Reimbursement'],
            ['type' => 'Itemized'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_types');
    }
};
