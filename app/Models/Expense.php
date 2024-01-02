<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    /**
     * Defines the Expense to Group relationship.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Defines the Expense to ExpenseType relationship.
     */
    public function type()
    {
        return $this->belongsTo(ExpenseType::class);
    }

    /**
     * Defines the Expense to Category relationship.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Defines the Expense to ExpenseParticipant (User) relationship.
     */
    public function participants()
    {
        return $this->belongsToMany(User::class, 'expense_participants', 'expense_id', 'user_id');
    }

    protected $fillable = [
        'name',
        'amount',
        'payer',
        'group_id',
        'expense_type_id',
        'category_id',
        'note',
        'date',
        'creator',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payer' => 'int',
        'group_id' => 'int',
        'expense_type_id' => 'int',
        'category_id' => 'int',
        'creator' => 'int',
    ];
}
