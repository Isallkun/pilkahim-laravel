<?php

namespace App\Console\Commands;

use App\Models\Election;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoCloseElections extends Command
{
    protected $signature = 'elections:auto-close';

    protected $description = 'Automatically close elections that have passed their end date';

    public function handle(): int
    {
        $count = Election::where('status', 'active')
            ->where('end_date', '<', now())
            ->update(['status' => 'finished']);

        if ($count > 0) {
            Log::info("AutoCloseElections: {$count} election(s) closed automatically.");
            $this->info("{$count} election(s) closed.");
        } else {
            $this->info('No elections to close.');
        }

        return self::SUCCESS;
    }
}
