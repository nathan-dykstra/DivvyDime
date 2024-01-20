<?php

namespace App\Models;

use App\Events\FriendCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    use HasFactory;

    protected $fillable = [
        'user1_id',
        'user2_id',
    ];

    protected $casts = [
        'user1_id' => 'int',
        'user2_id' => 'int',
    ];

    protected $dispatchesEvents = [
        'created' => FriendCreated::class,
    ];
}
