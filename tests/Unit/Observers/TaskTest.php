<?php

namespace Tests\Unit\Observers;

use App\Models\Task;
use App\Models\Terminal;
use App\Models\Worklog;
use App\TaskPriorityType;
use App\TaskQueueType;
use App\TaskSourceType;
use App\TaskStateType;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TaskTest extends TestCase
{
    public function test_it_sets_default_queue_when_none_provided(): void
    {
        // Arrange
        $task = Task::factory()->make(['queue' => null]);

        // Act
        $task->save();

        // Assert
        $this->assertEquals(TaskQueueType::Unscheduled, $task->queue);
    }

    public function test_it_applies_a_default_priority_to_a_task_when_none_is_provided(): void
    {
        // Arrange
        $task = Task::factory()->make(['priority' => null]);

        // Act
        $task->save();

        // Assert
        $this->assertEquals(TaskPriorityType::default(), $task->priority);
    }

    public function test_it_applies_a_default_state_to_a_task_when_none_is_provided(): void
    {
        // Arrange
        $task = Task::factory()->make(['state' => null]);

        // Act
        $task->save();

        // Assert
        $this->assertEquals(TaskStateType::default(), $task->state);
    }

    public function test_it_applies_a_default_source_to_a_task_when_none_is_provided(): void
    {
        // Arrange
        $task = Task::factory()->make(['source' => null]);

        // Act
        $task->save();

        // Assert
        $this->assertEquals(TaskSourceType::default(), $task->source);
    }

    public function test_it_sets_task_status_to_stopped_on_all_other_started_tasks_when_task_status_set_to_started(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $task = Task::factory()->for($terminal)->create(['state' => TaskStateType::Stopped]);
        $otherTask = Task::factory()->for($terminal)->create(['state' => TaskStateType::Started]);

        // Act
        $task->state = TaskStateType::Started;
        $task->save();

        // Assert
        $this->assertEquals(TaskStateType::Stopped, $otherTask->fresh()->state);
    }

    public function test_it_ends_all_worklogs_when_task_status_set_to_inactive_state(): void
    {
        Carbon::setTestNow(now());

        // Arrange
        $task = Task::factory()->create(['state' => TaskStateType::Started]);
        $worklog = Worklog::factory()->create(['task_id' => $task->id]);

        // Act
        $task->state = TaskStateType::Stopped;
        $task->save();

        // Assert
        $this->assertDatabaseMissing('worklogs', ['id' => $worklog->id, 'ended_at' => null]);
        $this->assertEquals(now()->toDateTimeString(), $worklog->fresh()->ended_at);
    }
}
