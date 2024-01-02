<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_id',
        'user_id',
        'share',
        'percentage',
        'shares',
        'adjustment',
        'is_settled',
    ];

    protected $casts = [
        'expense_id' => 'int',
        'user_id' => 'int',
        'share' => 'decimal:2',
        'percentage' => 'int',
        'shares' => 'decimal:1',
        'adjustment' => 'decimal:2',
        'is_settled' => 'bool',
    ];
}
