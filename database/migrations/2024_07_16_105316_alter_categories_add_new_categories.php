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
        Schema::table('categories', function (Blueprint $table) {
            Category::where('id', 1)->update([
                'category' => 'Household',
                'icon_class' => 'fa-solid fa-toilet-paper'
            ]);
            Category::where('id', 2)->update([
                'icon_class' => 'fa-solid fa-house'
            ]);
            Category::where('id', 3)->update([
                'icon_class' => 'fa-solid fa-house'
            ]);
            Category::where('id', 4)->update([
                'icon_class' => 'fa-solid fa-couch'
            ]);
            Category::where('id', 5)->update([
                'category' => 'Maintenance',
                'icon_class' => 'fa-solid fa-screwdriver-wrench'
            ]);
            Category::insert([
                [
                    'category_group_id' => CategoryGroup::HOME,
                    'category' => 'Home Improvement',
                    'icon_class' => 'fa-solid fa-paint-roller'
                ]
            ]);
            Category::where('id', 6)->update([
                'icon_class' => 'fa-solid fa-plug'
            ]);
            Category::where('id', 7)->update([
                'icon_class' => 'fa-solid fa-paw'
            ]);
            Category::where('id', 8)->update([
                'icon_class' => 'fa-solid fa-receipt'
            ]);
            Category::where('id', 9)->update([
                'icon_class' => 'fa-solid fa-film'
            ]);
            Category::where('id', 10)->update([
                'icon_class' => 'fa-solid fa-music'
            ]);
            Category::where('id', 11)->update([
                'icon_class' => 'fa-solid fa-basketball'
            ]);
            Category::where('id', 12)->update([
                'icon_class' => 'fa-solid fa-gamepad'
            ]);
            Category::where('id', 13)->update([
                'icon_class' => 'fa-solid fa-receipt'
            ]);
            Category::where('id', 14)->update([
                'icon_class' => 'fa-solid fa-cart-shopping'
            ]);
            Category::where('id', 15)->update([
                'icon_class' => 'fa-solid fa-utensils'
            ]);
            Category::where('id', 16)->update([
                'category' => 'Liquor',
                'icon_class' => 'fa-solid fa-wine-glass'
            ]);
            Category::insert([
                [
                    'category_group_id' => CategoryGroup::FOOD_AND_DRINK,
                    'category' => 'Take-Out & Delivery',
                    'icon_class' => 'fa-solid fa-pizza-slice'
                ]
            ]);
            Category::where('id', 17)->update([
                'icon_class' => 'fa-solid fa-receipt'
            ]);
            Category::where('id', 18)->update([
                'icon_class' => 'fa-solid fa-gas-pump'
            ]);
            Category::where('id', 19)->update([
                'icon_class' => 'fa-solid fa-square-parking'
            ]);
            Category::where('id', 20)->update([
                'icon_class' => 'fa-solid fa-taxi'
            ]);
            Category::where('id', 21)->update([
                'category' => 'Accommodations',
                'icon_class' => 'fa-solid fa-people-roof'
            ]);
            Category::where('id', 22)->update([
                'category' => 'Bicycle',
                'icon_class' => 'fa-solid fa-bicycle'
            ]);
            Category::where('id', 23)->update([
                'category' => 'Bus & Train',
                'icon_class' => 'fa-solid fa-bus'
            ]);
            Category::where('id', 24)->update([
                'category' => 'Car',
                'icon_class' => 'fa-solid fa-car'
            ]);
            Category::where('id', 25)->update([
                'icon_class' => 'fa-solid fa-plane'
            ]);
            Category::insert([
                [
                    'category_group_id' => CategoryGroup::TRAVEL_AND_TRANSPORTATION,
                    'category' => 'Vacation',
                    'icon_class' => 'fa-solid fa-umbrella-beach'
                ]
            ]);
            Category::where('id', 26)->update([
                'icon_class' => 'fa-solid fa-receipt'
            ]);
            Category::where('id', 27)->update([
                'icon_class' => 'fa-solid fa-wifi'
            ]);
            Category::where('id', 28)->update([
                'category' => 'Water',
                'icon_class' => 'fa-solid fa-shower'
            ]);
            Category::where('id', 29)->update([
                'icon_class' => 'fa-solid fa-bolt'
            ]);
            Category::where('id', 30)->update([
                'icon_class' => 'fa-solid fa-fire'
            ]);
            Category::where('id', 31)->update([
                'category' => 'Lawn Care',
                'icon_class' => 'fa-solid fa-faucet-drip'
            ]);
            Category::where('id', 32)->update([
                'icon_class' => 'fa-solid fa-snowplow'
            ]);
            Category::where('id', 33)->update([
                'icon_class' => 'fa-solid fa-trash-can'
            ]);
            Category::where('id', 34)->update([
                'icon_class' => 'fa-solid fa-hand-sparkles'
            ]);
            Category::where('id', 35)->update([
                'icon_class' => 'fa-solid fa-receipt'
            ]);
            Category::where('id', 36)->update([
                'icon_class' => 'fa-solid fa-receipt'
            ]);
            Category::where('id', 37)->update([
                'icon_class' => 'fa-solid fa-hand-holding-dollar'
            ]);
            Category::insert([
                [
                    'category_group_id' => CategoryGroup::LIFE,
                    'category' => 'Clothing',
                    'icon_class' => 'fa-solid fa-shirt'
                ],
                [
                    'category_group_id' => CategoryGroup::LIFE,
                    'category' => 'Education',
                    'icon_class' => 'fa-solid fa-school'
                ],
                [
                    'category_group_id' => CategoryGroup::LIFE,
                    'category' => 'Gifts',
                    'icon_class' => 'fa-solid fa-gift'
                ],
                [
                    'category_group_id' => CategoryGroup::LIFE,
                    'category' => 'Health',
                    'icon_class' => 'fa-solid fa-stethoscope'
                ],
                [
                    'category_group_id' => CategoryGroup::LIFE,
                    'category' => 'Hygiene',
                    'icon_class' => 'fa-solid fa-soap'
                ],
                [
                    'category_group_id' => CategoryGroup::LIFE,
                    'category' => 'Insurance',
                    'icon_class' => 'fa-solid fa-file-contract'
                ],
                [
                    'category_group_id' => CategoryGroup::LIFE,
                    'category' => 'Other',
                    'icon_class' => 'fa-solid fa-receipt'
                ]
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
