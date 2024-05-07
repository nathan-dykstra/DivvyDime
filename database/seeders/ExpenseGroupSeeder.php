<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpenseGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('expense_groups')->insert([
            ['expense_id' => 9, 'group_id' => 17],
            ['expense_id' => 10, 'group_id' => 17],
            ['expense_id' => 13, 'group_id' => 1],
            ['expense_id' => 26, 'group_id' => 1],
            ['expense_id' => 28, 'group_id' => 19],
            ['expense_id' => 29, 'group_id' => 1],
            ['expense_id' => 30, 'group_id' => 17],
            ['expense_id' => 35, 'group_id' => 1],
            ['expense_id' => 39, 'group_id' => 17],
            ['expense_id' => 42, 'group_id' => 17],
            ['expense_id' => 43, 'group_id' => 17],
        ]);
    }
}
