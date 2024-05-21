<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\EmailPreferenceType;
use App\Models\Friend;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $email_preference_options = [
            EmailPreferenceType::WEEKLY => 'Weekly',
            EmailPreferenceType::BIWEEKLY => 'Biweekly',
            EmailPreferenceType::MONTHLY => 'Monthly',
            EmailPreferenceType::NEVER => 'Never'
        ];

        $groups = $request->user()->groups()->whereNot('groups.id', Group::DEFAULT_GROUP)->get();
    
        return view('profile.edit', [
            'user' => $request->user()->load('preferences'),
            'email_preference_options' => $email_preference_options,
            'groups' => $groups,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     * TODO: This will need to be updated to delete records tied to this user by foreign ID
     *       Consider created a "User Deleted" user to update any expenses, groups, etc. so they are still visible rather than deleteing those too
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $user->deleteProfileImage();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
