<?php

namespace App\Models;

use App\Events\UserDeleting;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Defines the User to UserPreference relationship.
     */
    public function preferences()
    {
        return $this->hasOne(UserPreference::class);
    }

    /**
     * Defines the User to Group relationship.
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members', 'user_id', 'group_id');
    }

    /**
     * Returns the user's friends.
     */
    public function friends()
    {
        $friend_ids = Friend::select('user2_id AS friend_id')
            ->where('user1_id', $this->id)
            ->union(
                Friend::select('user1_id AS friend_id')
                    ->where('user2_id', $this->id)
            )
            ->get()->toArray();

        $friends = User::whereIn('id', $friend_ids);

        return $friends;
    }

    /**
     * Returns all expenses the user is involved in.
     */
    public function expenses()
    {
        $user_id = $this->id;

        $expenses_as_payer = Expense::where('payer', $user_id);

        $expenses_as_participant = Expense::whereHas('participants', function ($query) use ($user_id){
            $query->where('users.id', $user_id);
        });

        $all_expenses = $expenses_as_payer->union($expenses_as_participant)
            ->distinct();

        return $all_expenses;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'profile_img_file',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $dispatchesEvents = [
        'deleting' => UserDeleting::class,
    ];
}
