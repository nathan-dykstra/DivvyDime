<?php

namespace App\Http\Middleware;

use App\Models\Expense;
use App\Models\Group;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $current_user = $request->user();

        if ($request->route('payment_id') || $request->route('payment')) {
            $payment = null;

            if ($request->route('payment_id')) {
                $payment = Expense::find($request->route('payment_id'));
            } else {
                $payment = $request->route('payment');
            }

            if ($payment == null) {
                abort(404, "Uh oh! This payment doesn't exist.");
            } else if (($request->routeIs('payments.edit') || $request->routeIs('payments.update') || $request->routeIs('payments.destroy')) && $current_user->id !== $payment->creator) {
                // TODO: Remove this condition if I allow anyone else who has access to the payment to update/delete it (currently only creator has these privileges)
                abort(403, "Uh oh! Only the payment creator can modify or delete this payment.");
            } else if ($payment->groups()->where('groups.id', Group::DEFAULT_GROUP)->exists()) {
                if (!in_array($current_user->id, $payment->involvedUsers()->pluck('id')->toArray())) {
                    abort(403, "Uh oh! You're not involved in this payment.");
                }
            } else {
                $payment_groups = $payment->groups()->get();
                $in_payment_group = false;
                foreach($payment_groups as $payment_group) {
                    if (in_array($current_user->id, $payment_group->members()->pluck('users.id')->toArray())) {
                        $in_payment_group = true;
                        break;
                    }
                }
                if (!$in_payment_group) {
                    abort(403, "Uh oh! You're not involved in this payment.");
                }
            }
        }

        if ($request->routeIs('payments.*')) {
            if ($request->input('group')) {
                $group = Group::find($request->input('group'));
    
                if (!in_array($current_user->id, $group->members()->pluck('users.id')->toArray())) {
                    abort(403, "Uh oh! You can't create a payment in that group.");
                }
            }

            if ($request->input('friend')) {
                $friend = User::find($request->input('friend'));
    
                if (!in_array($current_user->id, $friend?->friends()->pluck('users.id')->toArray() ?? [])) {
                    abort(403, "Uh oh! You can't create a payment with that user.");
                }
            }
        }

        return $next($request);
    }
}
