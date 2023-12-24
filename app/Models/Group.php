<?php

namespace App\Models;

use App\Events\GroupDeleting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    const DEFAULT_GROUP = 1;

    use HasFactory;

    /**
     * Defines the Group to GroupMember relationship.
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members');
    }

    protected $fillable = [
        'name',
        'img_file',
        'owner',
    ];

    protected $casts = [
        'owner' => 'int',
    ];

    protected $dispatchesEvents = [
        'deleting' => GroupDeleting::class,
    ];
}
