<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasFactory;

    /**
     * Defines the UserPreference to User relationship.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    protected $fillable = [
        'email_preference_type_id',
    ];

    protected $casts = [
        'email_preference_type_id' => 'int',
    ];
}
