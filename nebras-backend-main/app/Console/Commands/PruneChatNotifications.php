<?php

namespace App\Console\Commands;

use App\Models\ChatNotification;
use Illuminate\Console\Command;

class PruneChatNotifications extends Command
{
    protected $signature = 'chat:prune-notifications {--days=30}';
    protected $description = 'Delete read chat notifications older than the given days (default 30).';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);
        $deleted = ChatNotification::where('is_read', true)
            ->where('updated_at', '<', $cutoff)
            ->delete();

        $this->info("Pruned {$deleted} chat notifications older than {$days} days.");
        return self::SUCCESS;
    }
}
