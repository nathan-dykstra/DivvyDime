<?php

namespace App\Models;

use App\Events\GroupMemberCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'user_id',
    ];

    protected $casts = [
        'group_id' => 'int',
        'user_id' => 'int',
    ];

    protected $dispatchesEvents = [
        'created' => GroupMemberCreated::class,
    ];
}
