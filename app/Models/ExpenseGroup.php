<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseGroup extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'expense_id',
        'group_id',
    ];

    protected $casts = [
        'expense_id' => 'int',
        'group_id' => 'int',
    ];
}
