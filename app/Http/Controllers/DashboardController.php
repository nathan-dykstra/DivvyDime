<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function show(Request $request): View
    {
        $now = Carbon::now();
        $hour = $now->hour;
        $greeting = '';

        if ($hour < 12) {
            $greeting = __('Good morning');
        } elseif ($hour >= 12 && $hour < 17) {
            $greeting = __('Good afternoon');
        } else {
            $greeting = __('Good evening');
        }

        return view('dashboard', [
            'current_user' => $request->user(),
            'greeting' => $greeting,
        ]);
    }
}
