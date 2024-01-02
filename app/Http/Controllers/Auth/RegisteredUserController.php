<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Friend;
use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\GroupMember;
use App\Models\Invite;
use App\Models\Notification;
use App\Models\NotificationAttribute;
use App\Models\NotificationType;
use App\Models\User;
use App\Models\UserPreference;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Display the registration from invite view.
     */
    public function createFromInvite($token): View
    {
        $invite = Invite::where('token', $token)->first();
        return view('auth.registerfrominvite', ['invite' => $invite]);
    }

    /**
     * Display the registration from group invite view.
     */
    public function createFromGroupInvite($token): View
    {
        $invite = GroupInvite::where('token', $token)->first();
        return view('auth.registerfrominvite', ['invite' => $invite]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Create the new User
        $user = $this->createUser($request);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }

    /**
     * Handle an incoming registration request from a friend invite.
     */
    public function storeFromInvite(Request $request, $token = null): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Create the new User
        $user = $this->createUser($request);

        if ($token) {
            $invite = Invite::where('token', $token)->first();

            Friend::create([
                'user1_id' => $user->id,
                'user2_id' => $invite->inviter,
            ]);

            // Send inviter and invitee a "friend request accepted" notification

            Notification::create([
                'notification_type_id' => NotificationType::FRIEND_REQUEST_ACCEPTED,
                'creator' => $user->id,
                'sender' => $user->id,
                'recipient' => $invite->inviter,
            ]);

            Notification::create([
                'notification_type_id' => NotificationType::FRIEND_REQUEST_ACCEPTED,
                'creator' => $user->id,
                'sender' => $invite->inviter,
                'recipient' => $user->id,
            ]);

            $invite->delete();
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }

    /**
     * Handle an incoming registration request from a Group invite.
     */
    public function storeFromGroupInvite(Request $request, $token = null): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Create the new User
        $user = $this->createUser($request);

        if ($token) {
            $invite = GroupInvite::where('token', $token)->first();

            $group = Group::where('id', $invite->group_id)->first();

            
            foreach ($group->members()->pluck('users.id')->toArray() as $member_id) {
                // Add each member of the group as a Friend on the app
                Friend::create([
                    'user1_id' => $user->id,
                    'user2_id' => $member_id,
                ]);

                // Send each member a "joined Group" notification

                $member_notification = Notification::create([
                    'notification_type_id' => NotificationType::JOINED_GROUP,
                    'creator' => $user->id,
                    'sender' => $user->id,
                    'recipient' => $member_id,
                ]);
    
                NotificationAttribute::create([
                    'notification_id' => $member_notification->id,
                    'group_id' => $group->id,
                ]);
            }

            // Add the new User to the Group
            GroupMember::create([
                'group_id' => $group->id,
                'user_id' => $user->id,
            ]);

            // Send the invitee a "joined Group" notification

            $invitee_notification = Notification::create([
                'notification_type_id' => NotificationType::JOINED_GROUP,
                'creator' => $user->id,
                'sender' => $user->id,
                'recipient' => $user->id,
            ]);

            NotificationAttribute::create([
                'notification_id' => $invitee_notification->id,
                'group_id' => $group->id,
            ]);

            $invite->delete();
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }

    /**
     * Creates a new User and initial User configurations
     */
    protected function createUser(Request $request): User
    {
        // Create the new User
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create UserPreference for the new User
        $user_preference = new UserPreference();
        $user->preferences()->save($user_preference);

        // Add the new User to the default Group
        GroupMember::create([
            'group_id' => Group::DEFAULT_GROUP,
            'user_id' => $user->id,
        ]);

        return $user;
    }
}
