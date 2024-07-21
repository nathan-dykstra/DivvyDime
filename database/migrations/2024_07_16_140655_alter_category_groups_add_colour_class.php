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
        Schema::table('category_groups', function (Blueprint $table) {
            $table->string('colour_class', 255)->after('group')->nullable();
        });

        CategoryGroup::where('id', 1)->update([
            'colour_class' => 'blue-background-text-border'
        ]);
        CategoryGroup::where('id', 2)->update([
            'colour_class' => 'pink-background-text-border'
        ]);
        CategoryGroup::where('id', 3)->update([
            'colour_class' => 'green-background-text-border'
        ]);
        CategoryGroup::where('id', 4)->update([
            'colour_class' => 'yellow-background-text-border'
        ]);
        CategoryGroup::where('id', 5)->update([
            'colour_class' => 'orange-background-text-border'
        ]);
        CategoryGroup::where('id', 6)->update([
            'colour_class' => 'grey-background-text-border'
        ]);
        CategoryGroup::where('id', 7)->update([
            'colour_class' => 'purple-background-text-border'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('category_groups', function (Blueprint $table) {
            $table->dropColumn('colour_class');
        });
    }
};
