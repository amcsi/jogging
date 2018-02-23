<?php

use App\JoggingTime;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $joggingTimes = [];

        // Create many users.
        factory(User::class, 50)->create()->each(function (User $user) use (&$joggingTimes) {
            if ($user->id == 1) {
                // The first user should have a fixed email for demoing purposes.
                $user->email = 'admin@example.com';
                $user->role = User\Role::ADMIN;
                $user->save();
            }
            $date = new Carbon('-1 day');
            // Prepare the jogging times for each user.
            // Each date entry for each jogging time of a user will be from "today" counting backwards one day each.
            for ($i = 0; $i < 30; $i++) {
                if (random_int(1, 5) <= 2) {
                    // 2 in 5 chance to leave a gap in jogging times.
                    continue;
                }
                $_date = clone $date;
                $_date->subDays($i);
                $joggingTime = factory(JoggingTime::class)->make([
                    'user_id' => $user->id,
                    'day' => $_date->format('Y-m-d')
                ])->toArray();
                $joggingTimes[] = $joggingTime;
            }
        });
        // This is the maximum chunk size that the seems to be accepted by the database.
        $chunkSize = 249;

        // Insert the many jogging times in chunks.
        foreach (array_chunk($joggingTimes, $chunkSize) as $joggingTimesChunk) {
            JoggingTime::insert($joggingTimesChunk);
        }
    }
}
