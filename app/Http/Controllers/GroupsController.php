<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGroupRequest;
use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class GroupsController extends Controller
{
    /**
     * Displays the user's Groups
     */
    public function index()
    {
        $current_user = auth()->user();

        $groups = $current_user->groups()->get();

        return view('groups.groups-list', [
            'groups' => $groups,
        ]);
    }

    public function create(): View
    {
        return view('groups.create', ['group' => null]);
    }

    public function store(CreateGroupRequest $request): RedirectResponse
    {
        $current_user = auth()->user();

        $group = Group::create($request->validated());

        $group->owner = $current_user->id;
        $group->save();

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $current_user->id,
        ]);

        return Redirect::route('groups.show', $group->id)->with('status', 'group-created');
    }

    public function show($group_id): View
    {
        $current_user = auth()->user();

        $group = Group::where('id', $group_id)->first();

        if ($group === null) {
            return view('groups.does-not-exist');
        } else if (!in_array($current_user->id, $group->members()->pluck('users.id')->toArray())) {
            return view('groups.not-allowed');
        }

        return view('groups.show', [
            'group' => $group,
        ]);
    }

    public function settings(Group $group): View
    {
        return view('groups.group-settings', [
            'group' => $group,
        ]);
    }

    public function invite(Request $request, $group_id): RedirectResponse
    {
        /*$request->validateWithBag('groupInvite', [
            'user_email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ]);*/

        $inviter = $request->user();
    
        return Redirect::route('groups.settings', $group_id)->with('status', 'invite-sent');
    }

    /**
     * Filters the Groups list.
     */
    public function search(Request $request): View
    {
        $current_user = auth()->user();

        $search_string = $request->input('search_string');

        $groups = $current_user->groups()
            ->join('users', 'group_members.user_id', 'users.id')
            ->where(function ($query) use ($search_string) {
                $query->whereRaw('users.username LIKE ?', ["%$search_string%"])
                    ->orWhereRaw('users.email LIKE ?', ["%$search_string%"])
                    ->orWhereRaw('groups.name LIKE ?', ["%$search_string%"]);
            })
            ->get();

        return view('groups.partials.groups', ['groups' => $groups]);
    }
}
