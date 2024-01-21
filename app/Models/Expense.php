<?php

namespace App\Models;

use App\Events\ExpenseDeleting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    /**
     * Defines the Expense to Group relationship.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Defines the Expense to ExpenseType relationship.
     */
    public function type()
    {
        return $this->belongsTo(ExpenseType::class);
    }

    /**
     * Defines the Expense to Category relationship.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Defines the Expense to ExpenseParticipant (User) relationship.
     */
    public function participants()
    {
        return $this->belongsToMany(User::class, 'expense_participants', 'expense_id', 'user_id');
    }

    /**
     * Returns all the Users involved in the Expense (as a payer or as a participant)
     */
    public function involvedUsers()
    {
        $payer = User::where('id', $this->payer);

        $participants = $this->participants()->select('users.*');

        $involved_users = $payer->union($participants)
            ->orderByRaw("
                CASE
                    WHEN id = ? THEN 0
                    ELSE 1
                END, username ASC
            ", [auth()->user()->id])
            ->get();

        $involved_users = $involved_users->map(function ($involved_user) {
            $involved_user->participant_amount = ExpenseParticipant::where('expense_id', $this->id)
                ->where('user_id', $involved_user->id)
                ->value('share');

            return $involved_user;
        });

        return $involved_users;
    }

    /**
     * Undo the Balance adjustments that were made when this Expense was created/updated
     */
    public function undoBalanceAdjustments()
    {
        // TODO: This function may need to be adjusted to support reimbursements
        foreach($this->participants()->get() as $participant) {
            if ($participant->id !== $this->payer) {
                $participant_share = ExpenseParticipant::where('expense_id', $this->id)
                    ->where('user_id', $participant->id)
                    ->value('share');

                // Increase the participant to payer Balance by the participant's share

                $participant_payer_balance = Balance::where('user_id', $participant->id)
                    ->where('friend', $this->payer)
                    ->where('group_id', $this->group_id)
                    ->first();

                $participant_payer_balance->increment('balance', $participant_share);

                // Decrease the payer to participant Balance by the participant's share

                $payer_participant_balance = Balance::where('user_id', $this->payer)
                    ->where('friend', $participant->id)
                    ->where('group_id', $this->group_id)
                    ->first();

                $payer_participant_balance->decrement('balance', $participant_share);
            }
        }
    }

    protected $fillable = [
        'name',
        'amount',
        'payer',
        'group_id',
        'expense_type_id',
        'category_id',
        'note',
        'date',
        'creator',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payer' => 'int',
        'group_id' => 'int',
        'expense_type_id' => 'int',
        'category_id' => 'int',
        'creator' => 'int',
    ];

    protected $dispatchesEvents = [
        'deleting' => ExpenseDeleting::class,
    ];
}
