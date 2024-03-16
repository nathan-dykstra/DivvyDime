<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    const DEFAULT_CATEGORY = 36;
    const PAYMENT_CATEGORY = 37;

    use HasFactory;

    /**
     * Defines the Category to Expense relationship.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public $timestamps = false;

    protected $fillable = [
        'category',
    ];
}
