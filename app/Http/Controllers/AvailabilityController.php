<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WeeklyAvailability;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AvailabilityController extends Controller
{
    public function setAvailability(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'day_of_week' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        WeeklyAvailability::create($request->only('user_id', 'day_of_week', 'start_time', 'end_time'));

        return response()->json(['message' => 'Availability set successfully.']);
    }

    public function getAvailability(Request $request, $user_id)
    {
        $request->validate(['timezone' => 'required|string']);

        $user = User::findOrFail($user_id);
        $availability = $user->availability;

        $converted = $availability->map(function ($slot) use ($request, $user) {
            $start = Carbon::createFromTimeString($slot->start_time, $user->timezone)->setTimezone($request->timezone);
            $end = Carbon::createFromTimeString($slot->end_time, $user->timezone)->setTimezone($request->timezone);
            return [
                'day_of_week' => $slot->day_of_week,
                'start_time' => $start->format('H:i'),
                'end_time' => $end->format('H:i'),
            ];
        });

        return response()->json($converted);
    }
}
