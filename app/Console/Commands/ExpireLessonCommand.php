<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lession;

class ExpireLessonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-lesson';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire lesson after 1 hour if not started';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lessons = Lession::where('status', 'approved')
            ->where('start_at', '<=', now()->subHour())
            ->get();
        
        foreach ($lessons as $lesson) {
            $lesson->update(['status' => 'finished']);
        }
    }
}
