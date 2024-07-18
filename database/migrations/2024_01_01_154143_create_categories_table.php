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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_group_id');
            $table->string('category', 100);
            $table->string('category_img_file')->nullable();

            $table->foreign('category_group_id')->references('id')->on('category_groups');
        });

        Category::insert([
            [
                'category_group_id' => CategoryGroup::HOME,
                'category' => 'Household Supplies',
            ],
            [
                'category_group_id' => CategoryGroup::HOME,
                'category' => 'Rent',
            ],
            [
                'category_group_id' => CategoryGroup::HOME,
                'category' => 'Mortgage',
            ],
            [
                'category_group_id' => CategoryGroup::HOME,
                'category' => 'Furniture',
            ],
            [
                'category_group_id' => CategoryGroup::HOME,
                'category' => 'Maintenance & Improvement',
            ],
            [
                'category_group_id' => CategoryGroup::HOME,
                'category' => 'Electronics',
            ],
            [
                'category_group_id' => CategoryGroup::HOME,
                'category' => 'Pets',
            ],
            [
                'category_group_id' => CategoryGroup::HOME,
                'category' => 'Other',
            ],
            [
                'category_group_id' => CategoryGroup::ENTERTAINMENT,
                'category' => 'Movies',
            ],
            [
                'category_group_id' => CategoryGroup::ENTERTAINMENT,
                'category' => 'Music',
            ],
            [
                'category_group_id' => CategoryGroup::ENTERTAINMENT,
                'category' => 'Sports',
            ],
            [
                'category_group_id' => CategoryGroup::ENTERTAINMENT,
                'category' => 'Games',
            ],
            [
                'category_group_id' => CategoryGroup::ENTERTAINMENT,
                'category' => 'Other',
            ],
            [
                'category_group_id' => CategoryGroup::FOOD_AND_DRINK,
                'category' => 'Groceries',
            ],
            [
                'category_group_id' => CategoryGroup::FOOD_AND_DRINK,
                'category' => 'Restaurant',
            ],
            [
                'category_group_id' => CategoryGroup::FOOD_AND_DRINK,
                'category' => 'Liquor & Alcohol',
            ],
            [
                'category_group_id' => CategoryGroup::FOOD_AND_DRINK,
                'category' => 'Other',
            ],
            [
                'category_group_id' => CategoryGroup::TRAVEL_AND_TRANSPORTATION,
                'category' => 'Gas',
            ],
            [
                'category_group_id' => CategoryGroup::TRAVEL_AND_TRANSPORTATION,
                'category' => 'Parking',
            ],
            [
                'category_group_id' => CategoryGroup::TRAVEL_AND_TRANSPORTATION,
                'category' => 'Taxi',
            ],
            [
                'category_group_id' => CategoryGroup::TRAVEL_AND_TRANSPORTATION,
                'category' => 'Hotel',
            ],
            [
                'category_group_id' => CategoryGroup::TRAVEL_AND_TRANSPORTATION,
                'category' => 'Airbnb',
            ],
            [
                'category_group_id' => CategoryGroup::TRAVEL_AND_TRANSPORTATION,
                'category' => 'Public Transportation',
            ],
            [
                'category_group_id' => CategoryGroup::TRAVEL_AND_TRANSPORTATION,
                'category' => 'Rental Vehicle',
            ],
            [
                'category_group_id' => CategoryGroup::TRAVEL_AND_TRANSPORTATION,
                'category' => 'Airplane',
            ],
            [
                'category_group_id' => CategoryGroup::TRAVEL_AND_TRANSPORTATION,
                'category' => 'Other',
            ],
            [
                'category_group_id' => CategoryGroup::UTILITIES_AND_SERVICES,
                'category' => 'Internet, Phone, & TV',
            ],
            [
                'category_group_id' => CategoryGroup::UTILITIES_AND_SERVICES,
                'category' => 'Water & Hot Water',
            ],
            [
                'category_group_id' => CategoryGroup::UTILITIES_AND_SERVICES,
                'category' => 'Electricity',
            ],
            [
                'category_group_id' => CategoryGroup::UTILITIES_AND_SERVICES,
                'category' => 'Heat & Gas',
            ],
            [
                'category_group_id' => CategoryGroup::UTILITIES_AND_SERVICES,
                'category' => 'Lawn Maintenance',
            ],
            [
                'category_group_id' => CategoryGroup::UTILITIES_AND_SERVICES,
                'category' => 'Snow Removal',
            ],
            [
                'category_group_id' => CategoryGroup::UTILITIES_AND_SERVICES,
                'category' => 'Trash Collection',
            ],
            [
                'category_group_id' => CategoryGroup::UTILITIES_AND_SERVICES,
                'category' => 'Cleaning',
            ],
            [
                'category_group_id' => CategoryGroup::UTILITIES_AND_SERVICES,
                'category' => 'Other',
            ],
            [
                'category_group_id' => CategoryGroup::OTHER,
                'category' => 'General',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
