<?php

namespace App\Console\Commands;

use App\Models\PendingUser;
use App\Models\User;
use App\Models\ZendyLog;
use Illuminate\Console\Command;

class NormalizeCampuses extends Command
{
    protected $signature = 'campuses:normalize
                            {--dry-run : Show what would change without writing to the database}';

    protected $description = 'Normalize free-text campus values on users, pending users, and activity logs to Buenavista / Tagum / Bay';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run — no changes will be saved.');
        }

        $targets = [
            'users' => User::class,
            'pending_users' => PendingUser::class,
            'zendy_logs' => ZendyLog::class,
        ];

        $totalUpdated = 0;

        foreach ($targets as $label => $modelClass) {
            $updated = 0;
            $rows = $modelClass::query()
                ->whereNotNull('campus')
                ->where('campus', '!=', '')
                ->get(['id', 'campus']);

            foreach ($rows as $row) {
                $current = $row->campus;
                $normalized = User::normalizeCampus($current);

                if ($normalized === null || $normalized === $current) {
                    continue;
                }

                $updated++;

                if (! $dryRun) {
                    $modelClass::whereKey($row->id)->update(['campus' => $normalized]);
                } else {
                    $this->line("  [{$label} #{$row->id}] \"{$current}\" → \"{$normalized}\"");
                }
            }

            $this->info(($dryRun ? 'Would update' : 'Updated')." {$updated} {$label} row(s).");
            $totalUpdated += $updated;
        }

        $this->newLine();
        $this->info(($dryRun ? 'Would normalize' : 'Normalized')." {$totalUpdated} row(s) total.");

        return self::SUCCESS;
    }
}
