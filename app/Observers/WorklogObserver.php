<?php

namespace App\Observers;

use App\Models\Worklog;

class WorklogObserver
{
    public function creating(Worklog $worklog): void
    {
        if ($worklog->duration === null) {
            $worklog->duration = 0;
        }

        if ($worklog->started_at === null) {
            $worklog->started_at = now();
        }
    }

    public function updating(Worklog $worklog): void
    {
        if ($worklog->isDirty('ended_at') && $worklog->ended_at !== null) {
            $worklog->duration = $worklog->started_at->diffInMinutes($worklog->ended_at);
        }
    }

    public function updated(Worklog $worklog): void
    {
        $worklog->task->total_minutes_spent += $worklog->duration;
        $worklog->task->save();
    }
}
