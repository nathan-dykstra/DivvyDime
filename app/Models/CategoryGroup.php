<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryGroup extends Model
{
    const ENTERTAINMENT = 2;
    const FOOD_AND_DRINK = 3;
    const HOME = 1;
    const LIFE = 7;
    const TRAVEL_AND_TRANSPORTATION = 4;
    const UTILITIES_AND_SERVICES = 5;
    const OTHER = 6;

    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'group',
    ];
}
