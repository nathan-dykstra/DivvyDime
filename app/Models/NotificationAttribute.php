<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationAttribute extends Model
{
    use HasFactory;

    /**
     * Defines the NotificationAttribute to Notification relationship.
     */
    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public $timestamps = false;

    protected $fillable = [
        'notification_id',
        //'group_id',
        //'expense_id',
        //'payment_id',

    ];

    protected $casts = [
        'notification_id' => 'int',
        //'group_id' => 'int',
        //'expense_id' => 'int',
        //'payment_id' => 'int',
    ];
}
