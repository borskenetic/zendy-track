<?php

use App\Models\PendingUser;
use App\Models\User;
use App\Models\ZendyLog;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Normalize free-text campus values to Buenavista / Tagum / Bay.
     */
    public function up(): void
    {
        $this->normalizeTable(User::class);
        $this->normalizeTable(PendingUser::class);
        $this->normalizeTable(ZendyLog::class);
    }

    public function down(): void
    {
        // Irreversible data cleanup — original free-text values are not restored.
    }

    private function normalizeTable(string $modelClass): void
    {
        $modelClass::query()
            ->whereNotNull('campus')
            ->where('campus', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($modelClass) {
                foreach ($rows as $row) {
                    $normalized = User::normalizeCampus($row->campus);

                    if ($normalized === null || $normalized === $row->campus) {
                        continue;
                    }

                    $modelClass::whereKey($row->id)->update(['campus' => $normalized]);
                }
            });
    }
};
