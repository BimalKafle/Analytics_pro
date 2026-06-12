<?php

use Illuminate\Support\Facades\Schedule;

// Re-sync video metadata for all connected accounts once a day.
Schedule::command('videos:sync-all')->dailyAt('03:00');
