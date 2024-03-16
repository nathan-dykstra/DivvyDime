<?php

use App\Models\Category;
use App\Models\CategoryGroup;
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
        Schema::table('cateogries', function (Blueprint $table) {
            Category::insert([
                [
                    'category_group_id' => CategoryGroup::UNCATEGORIZED,
                    'category' => 'Payment',
                ],
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cateogries', function (Blueprint $table) {
            //
        });
    }
};
