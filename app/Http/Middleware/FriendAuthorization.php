<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FriendAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $current_user = $request->user();

        if ($request->route('friend_id') || $request->route('friend')) {
            $friend = null;

            if ($request->route('friend_id')) {
                $friend = User::find($request->route('friend_id'));
            } else {
                $friend = $request->route('friend');
            }

            if ($friend === null) {
                abort(404, "Uh oh! This user doesn't exist!");
            } else if (!in_array($current_user->id, $friend->friends()->pluck('id')->toArray())) {
                abort(403, "Uh oh! You're not friends with this user.");
            }
        }

        return $next($request);
    }
}
