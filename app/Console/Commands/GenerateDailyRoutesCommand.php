<?php

namespace App\Console\Commands;

use App\Services\RouteScheduler;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateDailyRoutesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'routes:generate {date? : The date to generate routes for (YYYY-MM-DD), defaults to today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily scheduled stops for routes based on service days and frequency rules';

    /**
     * Execute the console command.
     */
    public function handle(RouteScheduler $scheduler): int
    {
        $dateInput = $this->argument('date');
        
        try {
            $date = $dateInput ? Carbon::parse($dateInput) : Carbon::today();
        } catch (\Exception $e) {
            $this->error("Invalid date format provided: {$dateInput}. Please use YYYY-MM-DD.");
            return Command::FAILURE;
        }

        $this->info("Generating route stops for {$date->toDateString()} ({$date->format('l')})...");
        
        $count = $scheduler->generateStopsForDate($date);

        $this->info("Successfully generated {$count} scheduled stops.");

        return Command::SUCCESS;
    }
}
