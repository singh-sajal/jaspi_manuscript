<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

class PruneNotifications extends Command
{
    protected $signature = 'notifications:prune {--days=90 : Delete notifications older than this many days}';

    protected $description = 'Delete in-app notifications that are older than the retention window.';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));

        $deleted = DatabaseNotification::query()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        $this->info("Pruned {$deleted} notification(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
