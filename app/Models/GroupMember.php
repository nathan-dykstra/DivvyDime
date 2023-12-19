<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    use HasFactory;

    /**
     * Defines the GroupMember to Group relationship.
     */
    public function group() {
        return $this->belongsTo(Group::class);
    }

    protected $fillable = [
        'group_id',
        'user_id',
    ];

    protected $casts = [
        'group_id' => 'int',
        'user_id' => 'int',
    ];
}
// TODO: Add a unique index on (group_id, user_id)