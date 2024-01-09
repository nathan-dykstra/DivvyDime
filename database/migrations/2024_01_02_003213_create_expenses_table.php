<?php

use App\Models\Category;
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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->unsignedBigInteger('payer');
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('expense_type_id')->default(ExpenseType::EQUAL);
            $table->unsignedBigInteger('category_id')->default(Category::DEFAULT_CATEGORY);
            $table->text('note')->nullable();
            $table->date('date');
            $table->unsignedBigInteger('creator');
            $table->timestamps();

            $table->foreign('payer')->references('id')->on('users');
            $table->foreign('group_id')->references('id')->on('groups');
            $table->foreign('expense_type_id')->references('id')->on('expense_types');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('creator')->references('id')->on('users');

            $table->index('name');
            $table->index('payer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
