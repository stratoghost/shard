<?php

namespace App\Events\Tasks;

use App\Models\Task;
use Illuminate\Foundation\Events\Dispatchable;

class TaskStoppedEvent
{
    use Dispatchable;

    public function __construct(public Task $task) {}
}
