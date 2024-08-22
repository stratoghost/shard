<?php

namespace App\Services;

use App\Exceptions\Common\ModelFieldNotNullableException;
use App\Exceptions\Common\ModelNotModifiableException;
use App\Models\Task;
use App\Models\Terminal;
use App\TaskStateType;

readonly class TaskManagementService
{
    public function __construct(protected Terminal $terminal) {}

    public function createTask(array $attributes): Task
    {
        return $this->terminal->tasks()->create($attributes);
    }

    public function deleteTask(Task $task): void
    {
        $task->delete();
    }

    public function updateTask(Task $task, array $attributes): void
    {
        if (array_key_exists('state', $attributes) && $task->state->isFinal()) {
            throw new ModelNotModifiableException;
        }

        if (array_key_exists('state', $attributes) && empty($attributes['state'])) {
            throw new ModelFieldNotNullableException;
        }

        if (array_key_exists('priority', $attributes) && empty($attributes['priority'])) {
            throw new ModelFieldNotNullableException;
        }

        $task->update($attributes);
    }

    public function createChildTask(Task $task, array $attributes): Task
    {
        return $task->children()->create($attributes);
    }

    public function stopAllActiveTasks(): void
    {
        $this->terminal->tasks()->where('state', TaskStateType::Started)->each(function (Task $task) {
            $task->state = TaskStateType::Stopped;
            $task->save();
        });
    }

    public function switchActiveTask(Task $activeTask, Task $inactiveTask): void
    {
        $activeTask->state = TaskStateType::Stopped;
        $activeTask->save();

        $inactiveTask->state = TaskStateType::Started;
        $inactiveTask->save();
    }
}
