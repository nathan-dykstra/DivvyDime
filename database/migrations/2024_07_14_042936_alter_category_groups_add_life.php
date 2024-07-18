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
            CategoryGroup::insert([
                [
                    'group' => 'Life',
                ],
            ]);

            CategoryGroup::where('id', CategoryGroup::OTHER)->update([
                'group' => 'Other'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
