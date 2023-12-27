<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupInvite extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'inviter',
        'group_id',
        'token',
    ];

    protected $casts = [
        'inviter' => 'int',
        'group_id' => 'int',
    ];
}
