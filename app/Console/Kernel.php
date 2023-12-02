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

        $schedule->command('diod:sync')
            ->timezone('Asia/Yekaterinburg')->hourlyAt("00")->when(function () {
                $settings = json_decode(ObjectNotation::where("key", "sync")->first()->value);
                $hour = Carbon::now()->setTimezone("Asia/Yekaterinburg")->hour;
                if ($hour === $settings->first_sync) {
                    return true;
                }

                if ($settings->is_second_sync === true && $hour === $settings->second_sync) {
                    return true;
                }

                return false;
            })
            ->sendOutputTo(\Storage::path('../logs/sync.log'))
            ->onSuccess(function (Stringable $output) {
                dump('has been synced');
            });

        $schedule->command('diod:wildberries:stocks')
            ->timezone('Asia/Yekaterinburg')
            ->hourlyAt("21")
            ->sendOutputTo(\Storage::path('../logs/sync_low_stocks.log'))
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
