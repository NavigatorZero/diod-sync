<?php

namespace App\Console;

use App\Models\ObjectNotation;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Stringable;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $schedule->command('diod:sync false')
            ->timezone('Asia/Yekaterinburg')->hourlyAt("00")->when(function () {
                return Carbon::now()->hour === json_decode(ObjectNotation::where("key", "sync")->first()->value)->first_sync
                    || Carbon::now()->hour === json_decode(ObjectNotation::where("key", "sync")->first()->value)->second_sync;
            })
            ->sendOutputTo(\Storage::path('../logs/sync.log'))
            ->onSuccess(function (Stringable $output) {
                dump('has been synced');
            });
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
