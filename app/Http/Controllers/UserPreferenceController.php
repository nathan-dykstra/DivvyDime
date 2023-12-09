<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreferenceUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class UserPreferenceController extends Controller
{
    /**
     * Update the user's preferences.
     */
    public function update(PreferenceUpdateRequest $request): RedirectResponse 
    {
        $user_preference = $request->user()->preference()->firstOrCreate([]);
        $user_preference->fill($request->validated());
        $user_preference->save();

        return Redirect::route('profile.edit')->with('status', 'preferences-updated');
    }
}
