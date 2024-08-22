<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\Worklog;
use App\TaskPriorityType;
use App\TaskQueueType;
use App\TaskSourceType;
use App\TaskStateType;

class TaskObserver
{
    public function creating(Task $task): void
    {
        if ($task->priority === null) {
            $task->priority = TaskPriorityType::default();
        }

        if ($task->state === null) {
            $task->state = TaskStateType::default();
        }

        if ($task->queue === null) {
            $task->queue = TaskQueueType::Unscheduled;
        }

        if ($task->source === null) {
            $task->source = TaskSourceType::default();
        }
    }

    public function updated(Task $task): void
    {
        if ($task->state === TaskStateType::Started) {
            Task::where('terminal_id', $task->terminal_id)
                ->where('id', '!=', $task->id)
                ->where('state', TaskStateType::Started)
                ->update(['state' => TaskStateType::Stopped]);
        }

        if (! $task->state->isActive()) {
            $task->worklogs()->each(fn (Worklog $worklog) => $worklog->update(['ended_at' => now()]));
        }
    }
}
