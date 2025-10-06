<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-sessions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired sessions and duplicates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Delete expired sessions
        $expired = now()->subMinutes(config('session.lifetime'));
        
        $deleted = DB::table('sessions')
            ->where('last_activity', '<=', $expired->timestamp)
            ->delete();
            
        $this->info("Deleted {$deleted} expired sessions.");
        
        // Delete duplicate sessions for users (keep the most recent)
        $duplicates = DB::table('sessions')
            ->select('user_id', DB::raw('COUNT(*) as count'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->having('count', '>', 1)
            ->get();
            
        foreach ($duplicates as $duplicate) {
            $latest = DB::table('sessions')
                ->where('user_id', $duplicate->user_id)
                ->orderBy('last_activity', 'desc')
                ->first();
                
            if ($latest) {
                $deleted = DB::table('sessions')
                    ->where('user_id', $duplicate->user_id)
                    ->where('id', '!=', $latest->id)
                    ->delete();
                    
                $this->info("Deleted {$deleted} duplicate sessions for user {$duplicate->user_id}.");
            }
        }
        
        $this->info('Session cleanup completed.');
    }
}
