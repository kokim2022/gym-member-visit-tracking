<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserVisit;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;


class UserVisitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getMembers()
    {
        return User::select('name', 'created_at')->get();
    }

    public function getMemberVisits()
    {
        return UserVisit::with('user')->orderBy('created_at', 'DESC')->get();
    }

    /**
     * Explanation:
     * AT TIME ZONE 'Asia/Bangkok': Adjusts the created_at field to the specified timezone.
     * whereBetween: Filters records for the entire day of July 12, 2024.
     * The +07 indicates the offset for Bangkok's timezone (UTC+7).
     */
    public function getUniqueVisitsByHour(Request $request)
    {
        $selectedDate = $request->input('selectedDate');

        $startDateTime = Carbon::parse("{$selectedDate} 00:00:00", 'Asia/Bangkok')->setTimezone('UTC');
        $endDateTime = Carbon::parse("{$selectedDate} 23:59:59", 'Asia/Bangkok')->setTimezone('UTC');

        $uniqueVisitsByHour = DB::table('user_visits')
            ->select(
                DB::raw('DATE_FORMAT(CONVERT_TZ(created_at, "+00:00", "+07:00"), "%Y-%m-%d %H:00:00") AS hour_slot'),
                DB::raw('COUNT(DISTINCT user_id) AS unique_visits')
            )
            ->whereBetween('created_at', [
                $startDateTime,
                $endDateTime
            ])
            ->groupBy('hour_slot')
            ->orderBy('hour_slot') // Change to order by hour_slot
            ->get();

        return  $uniqueVisitsByHour;
    }

    public function getUniqueVisitsByDayInCurrentMonth()
    {
        // Get the current month and year
        $startOfMonth = Carbon::now()->startOfMonth()->setTimezone('UTC');
        $endOfMonth = Carbon::now()->endOfMonth()->setTimezone('UTC');

        // Query to get unique visits grouped by day for the current month
        $uniqueVisitsByDay = DB::table('user_visits')
            ->select(
                DB::raw('DATE(CONVERT_TZ(created_at, "+00:00", "+07:00")) AS visit_date'),
                DB::raw('COUNT(DISTINCT user_id) AS unique_visits')
            )
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('visit_date')
            ->orderBy('visit_date')
            ->get();

        return response()->json($uniqueVisitsByDay); // Return the results as JSON
    }
}
