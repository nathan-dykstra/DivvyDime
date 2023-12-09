<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailPreferenceType extends Model
{
    const WEEKLY = 1;
    const BIWEEKLY = 2;
    const MONTHLY = 3;
    const NEVER = 4;

    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;
}
