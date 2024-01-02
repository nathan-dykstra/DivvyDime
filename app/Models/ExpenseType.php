<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseType extends Model
{
    const EQUAL = 1;
    const AMOUNT = 2;
    const PERCENTAGE = 3;
    const SHARE = 4;
    const ADJUSTMENT = 5;
    const REIMBURSEMENT = 6;
    const ITEMIZED = 7;

    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'type',
    ];
}
