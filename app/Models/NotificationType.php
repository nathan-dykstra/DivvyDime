<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationType extends Model
{
    const EXPENSE = 1;
    const REIMBURSEMENT = 2;
    const REMINDER = 3;
    const PAYMENT = 4;
    const PAYMENT_CONFIRMED = 5;
    const PAYMENT_REJECTED = 13;
    const BALANCE_SETTLED = 6;
    const FRIEND_REQUEST = 7;
    const FRIEND_REQUEST_ACCEPTED = 8;
    const INVITED_TO_GROUP = 9;
    const JOINED_GROUP = 10;
    const LEFT_GROUP = 11;
    const REMOVED_FROM_GROUP = 12;

    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'type',
    ];
}
