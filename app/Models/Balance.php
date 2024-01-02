<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'friend',
        'group_id',
        'balance',
    ];

    protected $casts = [
        'user_id' => 'int',
        'friend' => 'int',
        'group_id' => 'int',
        'dollar_amount' => 'decimal:2',
    ];
}
