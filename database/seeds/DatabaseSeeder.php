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
        factory(User::class, 50)->create()->each(function (User $user) {
            $date = Carbon::now();
            $joggingTimes = new Collection();
            for ($i = 0; $i < 30; $i++) {
                $_date = clone $date;
                $_date->subDays($i);
                $joggingTime = factory(JoggingTime::class)->make(['day' => $_date->format('Y-m-d')]);
                $joggingTimes->push($joggingTime);
            }
            $user->joggingTimes()->saveMany($joggingTimes);
        });
    }
}
