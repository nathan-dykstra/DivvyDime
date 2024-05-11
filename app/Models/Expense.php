<?php

namespace App\Models;

use App\Events\ExpenseDeleting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Expense extends Model
{
    use HasFactory;

    /**
     * Defines the Expense to Group relationship.
     */
    /*public function group()
    {
        return $this->belongsTo(Group::class);
    }*/
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'expense_groups');
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
     * Updates the balance records between $expense->payer and $user_id by $amount
     */
    public static function updateBalances(Expense $expense, $user_id, $amount)
    {
        if ($expense->expense_type_id === ExpenseType::SETTLE_ALL_BALANCES) {
            $expense_group_ids = $expense->groups->pluck('id')->toArray();

            $participant_payer_balances = Balance::where('user_id', $user_id)
                ->where('friend', $expense->payer)
                ->whereIn('group_id', $expense_group_ids)
                ->get();

            $payer_participant_balances = Balance::where('user_id', $expense->payer)
                ->where('friend', $user_id)
                ->whereIn('group_id', $expense_group_ids)
                ->get();

            // Decrement each participant to payer balance by the amount from that group in expense_groups
            // Note: Should bring the resulting balance to 0
            foreach ($participant_payer_balances as $balance) {
                $group_amount = ExpenseGroup::where('expense_id', $expense->id)
                    ->where('group_id', $balance->group_id)
                    ->value('group_amount');

                $balance->decrement('balance', $group_amount);
            }

            // Increment each payer to participant balance by the amount from that group in expense_groups
            // Note: Should bring the resulting balance to 0
            foreach ($payer_participant_balances as $balance) {
                $group_amount = ExpenseGroup::where('expense_id', $expense->id)
                    ->where('group_id', $balance->group_id)
                    ->value('group_amount');

                $balance->increment('balance', $group_amount);
            }
        } else {
            $participant_payer_balance = Balance::where('user_id', $user_id)
                ->where('friend', $expense->payer)
                ->where('group_id', $expense->groups->first()->id)
                ->first();

            $payer_participant_balance = Balance::where('user_id', $expense->payer)
                ->where('friend', $user_id)
                ->where('group_id', $expense->groups->first()->id)
                ->first();

            if ($expense->expense_type_id === ExpenseType::REIMBURSEMENT) { // Reverse the direction of the adjustments for reimbursement
                // Increase the participant to payer balance by the participant's share
                if ($participant_payer_balance) {
                    $participant_payer_balance->increment('balance', $amount);
                }

                // Decrease the payer to participant balance by the participant's share
                if ($payer_participant_balance) {
                    $payer_participant_balance->decrement('balance', $amount);
                }
            } else {
                // Decrease the participant to payer balance by the participant's share
                if ($participant_payer_balance) {
                    $participant_payer_balance->decrement('balance', $amount);
                }

                // Increase the payer to participant balance by the participant's share
                if ($payer_participant_balance) {
                    $payer_participant_balance->increment('balance', $amount);
                }
            }
        }
    }

    /**
     * Undo the Balance adjustments that were made when this Expense was created/updated
     */
    public function undoBalanceAdjustments()
    {
        foreach($this->participants()->get() as $participant) {
            if ($this->expense_type_id === ExpenseType::SETTLE_ALL_BALANCES) {
                $expense_group_ids = $this->groups->pluck('id')->toArray();

                $participant_payer_balances = Balance::where('user_id', $participant->id)
                    ->where('friend', $this->payer)
                    ->whereIn('group_id', $expense_group_ids)
                    ->get();

                $payer_participant_balances = Balance::where('user_id', $this->payer)
                    ->where('friend', $participant->id)
                    ->whereIn('group_id', $expense_group_ids)
                    ->get();

                // Increment each participant to payer balance by the amount from that group in expense_groups
                // Note: Should bring the resulting balance to 0
                foreach ($participant_payer_balances as $balance) {
                    $group_amount = ExpenseGroup::where('expense_id', $this->id)
                        ->where('group_id', $balance->group_id)
                        ->value('group_amount');

                    $balance->increment('balance', $group_amount);
                }

                // Decrement each payer to participant balance by the amount from that group in expense_groups
                // Note: Should bring the resulting balance to 0
                foreach ($payer_participant_balances as $balance) {
                    $group_amount = ExpenseGroup::where('expense_id', $this->id)
                        ->where('group_id', $balance->group_id)
                        ->value('group_amount');

                    $balance->decrement('balance', $group_amount);
                }
            } else {
                if ($participant->id !== $this->payer) {
                    $participant_share = ExpenseParticipant::where('expense_id', $this->id)
                        ->where('user_id', $participant->id)
                        ->value('share');

                    $participant_payer_balance = Balance::where('user_id', $participant->id)
                        ->where('friend', $this->payer)
                        ->where('group_id', $this->groups->first()->id)
                        ->first();

                    $payer_participant_balance = Balance::where('user_id', $this->payer)
                        ->where('friend', $participant->id)
                        ->where('group_id', $this->groups->first()->id)
                        ->first();

                    if ($this->expense_type_id === ExpenseType::REIMBURSEMENT) { // Reverse the direction of the adjustments
                        // Decrease the participant to payer Balance by the participant's share
                        if ($participant_payer_balance) {
                            $participant_payer_balance->decrement('balance', $participant_share);
                        }

                        // Increase the payer to participant Balance by the participant's share
                        if ($payer_participant_balance) {
                            $payer_participant_balance->increment('balance', $participant_share);
                        }
                    } else {
                        // Increase the participant to payer Balance by the participant's share
                        if ($participant_payer_balance) {
                            $participant_payer_balance->increment('balance', $participant_share);
                        }

                        // Decrease the payer to participant Balance by the participant's share
                        if ($payer_participant_balance) {
                            $payer_participant_balance->decrement('balance', $participant_share);
                        }
                    }
                }
            }
        }
    }

    /**
     * Send notifications to the users/group members involved in the expense or payment
     */
    public function sendExpenseNotifications()
    {
        if ($this->group_id === Group::DEFAULT_GROUP || $this->expense_type_id === ExpenseType::PAYMENT || $this->expense_type_id === ExpenseType::SETTLE_ALL_BALANCES) {
            // Only send the notification to involved Users

            foreach ($this->involvedUsers() as $involved_user) {
                $expense_notification = Notification::create([
                    'notification_type_id' => ($this->expense_type_id === ExpenseType::PAYMENT || $this->expense_type_id === ExpenseType::SETTLE_ALL_BALANCES) ? NotificationType::PAYMENT : NotificationType::EXPENSE,
                    'creator' => $this->creator,
                    'sender' => $this->creator,
                    'recipient' => $involved_user->id,
                ]);

                NotificationAttribute::create([
                    'notification_id' => $expense_notification->id,
                    'expense_id' => $this->id,
                ]);
            }
        } else {
            // Send the notification to all group members

            $group = $this->groups->first();

            foreach ($group->members()->get() as $member) {
                $expense_notification = Notification::create([
                    'notification_type_id' => NotificationType::EXPENSE,
                    'creator' => $this->creator,
                    'sender' => $this->creator,
                    'recipient' => $member->id,
                ]);

                NotificationAttribute::create([
                    'notification_id' => $expense_notification->id,
                    'expense_id' => $this->id,
                ]);
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
        'is_confirmed',
        'creator',
        'updator',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payer' => 'int',
        'group_id' => 'int',
        'expense_type_id' => 'int',
        'category_id' => 'int',
        'creator' => 'int',
        'updator' => 'int',
    ];

    protected $dispatchesEvents = [
        'deleting' => ExpenseDeleting::class,
    ];
}
