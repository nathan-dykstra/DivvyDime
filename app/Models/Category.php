<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    const DEFAULT_CATEGORY = 36;
    const PAYMENT_CATEGORY = 37;
    const OTHER_CATEGORY_IDS = [8, 13, 17, 26, 35, 47];

    use HasFactory;

    /**
     * Defines the Category to Expense relationship.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Defines the Category to CategoryGroup relationship.
     */
    public function categoryGroup()
    {
        return $this->belongsTo(CategoryGroup::class);
    }

    public $timestamps = false;

    protected $fillable = [
        'category',
    ];
}
