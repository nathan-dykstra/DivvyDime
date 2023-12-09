<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use App\Models\Invite;
use App\Models\User;
use App\Notifications\InviteNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class FriendsController extends Controller
{
    /**
     * Display the user's friends.
     */
    public function index(): View
    {
        $current_user = auth()->user();

        $friend_ids = Friend::select('user2_id AS friend_id')
            ->where('user1_id', $current_user->id)
            ->union(
                Friend::select('user1_id AS friend_id')
                    ->where('user2_id', $current_user->id)
            )
            ->get()->toArray();

        $friends = User::whereIn('id', $friend_ids)->get();

        return view('friends.friendslist', [
            'friends' => $friends,
        ]);
    }

    /**
     * Send a friend request.
     */
    public function invite(Request $request): RedirectResponse
    {
        $request->validateWithBag('friendInvite', [
            'friend_email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ]);

        $inviter = $request->user();

        $existing_user = User::where('email', $request->input('friend_email'))->first();

        if ($existing_user) {
            Log::info("existing");
        } else {
            Log::info("invited");
            do {
                $token = Str::random(20);
            } while (Invite::where('token', $token)->first());
    
            Invite::create([
                'token' => $token,
                'email' => $request->input('friend_email'),
                'inviter' => $inviter->id,
            ]);
    
            $url = URL::temporarySignedRoute(
                'register.frominvite', now()->addMinutes(300), ['token' => $token]
            );
    
            Notification::route('mail', $request->input('friend_email'))->notify(new InviteNotification($url, $inviter->username));
        }

        return Redirect::route('friends')->with('status', 'invite-sent');
    }
}
