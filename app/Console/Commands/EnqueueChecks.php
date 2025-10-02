<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Advert;
use App\Jobs\CheckAdvertJob;

class EnqueueChecks extends Command
{
    protected $signature = 'adverts:enqueue-checks {--interval=15}';
    protected $description = 'Enqueue adverts to be checked';

    public function handle()
    {
        $interval = (int)$this->option('interval');
        $threshold = now()->subMinutes($interval);
        $adverts = Advert::where('is_active', true)
            ->where(function ($query) use ($threshold) {
                $query->whereNull('last_checked_at')
                    ->orWhere('last_checked_at', '<', $threshold);
            })
            ->limit(500)
            ->get();
        foreach ($adverts as $advert) {
            CheckAdvertJob::dispatch($advert->id);
        }
    }
}
