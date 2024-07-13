<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserVisit;

class UserVisitSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {
            // Generate visit patterns for each user
            $visitStart = Carbon::createFromTime(rand(6, 20), rand(0, 59), 0, 'Asia/Bangkok')
                ->setTimezone('UTC'); // Set timezone to UTC for initial visit start

            // First visit
            UserVisit::create([
                'user_id' => $user->id,
                'created_at' => $visitStart,
            ]);

            // Determine if there will be another visit on the same day
            if (rand(0, 3) < 3) {
                // Ensure revisits are within the same hour or next hour
                $visitTime = $visitStart->copy()->addMinutes(rand(0, 60))->setTimezone('Asia/Bangkok');
                $visitTimeUTC = $visitTime->copy()->setTimezone('UTC');

                // Check if the revisit time is within open hours (6:00 AM to 9:00 PM)
                if ($visitTime->hour >= 6 && $visitTime->hour < 21) {
                    UserVisit::create([
                        'user_id' => $user->id,
                        'created_at' => $visitTimeUTC,
                    ]);
                }
            }
        }
    }
}
