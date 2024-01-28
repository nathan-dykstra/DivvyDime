<?php

namespace App\Http\Middleware;

use App\Models\Group;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GroupAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $current_user = $request->user();

        if ($request->route('group_id') || $group = $request->route('group')) {
            $group = null;

            if ($request->route('group_id')) {
                $group = Group::find($request->route('group_id'));
            } else {
                $group = $request->route('group');
            }

            if ($group === null) {
                abort(404, "Uh oh! This group doesn't exist!");
            } else if (!in_array($current_user->id, $group->members()->pluck('users.id')->toArray())) {
                abort(403, "Uh oh! You're not a member of this group.");
            }
        }

        return $next($request);
    }
}
