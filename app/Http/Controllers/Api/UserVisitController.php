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
        try {
            $selectedDate = $request->input('selectedDate');

            // // Validate the date format
            if (!$selectedDate || !Carbon::hasFormat($selectedDate, 'Y-m-d')) {
                return response()->json(['error' => 'Invalid date format.'], 400);
            }
            $startDateTime = Carbon::parse("{$selectedDate} 00:00:00", 'Asia/Bangkok')->setTimezone('UTC');
            $endDateTime = Carbon::parse("{$selectedDate} 23:59:59", 'Asia/Bangkok')->setTimezone('UTC');
            $sql = "WITH hourly_first_visit AS (
                SELECT
                    user_id,
                    DATE_FORMAT(CONVERT_TZ(created_at, '+00:00', '+07:00'), '%Y-%m-%d %H:00:00') AS hour_slot,
                    ROW_NUMBER() OVER (
                        PARTITION BY user_id
                        ORDER BY created_at
                    ) AS rn
                FROM
                    user_visits
                WHERE created_at BETWEEN :startDateTime AND :endDateTime
            )
            SELECT
                hour_slot,
                COUNT(user_id) AS unique_visits
            FROM
                hourly_first_visit
            WHERE
                rn = 1
            GROUP BY
                hour_slot
            ORDER BY
                hour_slot
        ";
            $results = DB::select($sql, [
                'startDateTime' => $startDateTime,
                'endDateTime' => $endDateTime
            ]); // Pass the SQL string directly
            return $results;
        } catch (\Exception $e) {
            // Return a JSON response with the error message
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
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
