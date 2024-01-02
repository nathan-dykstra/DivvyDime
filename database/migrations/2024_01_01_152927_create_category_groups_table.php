<?php

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
        Schema::create('category_groups', function (Blueprint $table) {
            $table->id();
            $table->string('group', 100);
        });

        CategoryGroup::insert([
            ['group' => 'Home'],
            ['group' => 'Entertainment'],
            ['group' => 'Food & Drink'],
            ['group' => 'Travel & Transportation'],
            ['group' => 'Utilities & Services'],
            ['group' => 'Uncategorized'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_groups');
    }
};
