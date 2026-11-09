<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\Application\ReviewDeadlineRepository;
use Illuminate\Console\Command;

class SyncReviewDeadlines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviewers:sync-deadlines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh per-reviewer deadline tracking and notify handling editors of reviewer timeouts';

    /**
     * Execute the console command.
     */
    public function handle(ReviewDeadlineRepository $repository): int
    {
        $repository->sync();
        $notified = $repository->notifyTimeouts();

        $this->info("Review deadlines synced. {$notified} timeout notification(s) sent.");

        return self::SUCCESS;
    }
}
