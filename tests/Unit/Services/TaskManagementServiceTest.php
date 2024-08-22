<?php

namespace Tests\Unit\Services;

use App\Exceptions\Common\ModelFieldNotNullableException;
use App\Exceptions\Common\ModelNotModifiableException;
use App\Models\Session;
use App\Models\Task;
use App\Models\Terminal;
use App\Models\Worklog;
use App\Services\TaskManagementService;
use App\TaskPriorityType;
use App\TaskStateType;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TaskManagementServiceTest extends TestCase
{
    public function test_it_creates_a_task(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $task = Task::factory()->for($terminal)->make();
        $taskManagerService = new TaskManagementService($terminal);

        // Act
        $task = $taskManagerService->createTask($task->attributesToArray());

        // Assert
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_it_creates_child_task(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $parentTask = Task::factory()->for($terminal)->create();
        $childTask = Task::factory()->for($terminal)->make();
        $taskManagerService = new TaskManagementService($terminal);

        // Act
        $childTask = $taskManagerService->createChildTask($parentTask, $childTask->attributesToArray());

        // Assert
        $this->assertDatabaseHas('tasks', ['id' => $childTask->id, 'parent_id' => $parentTask->id]);
    }

    public function test_it_can_update_the_details_of_a_task(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $task = Task::factory()->for($terminal)->create();
        $taskManagerService = new TaskManagementService($terminal);

        // Act
        $taskManagerService->updateTask($task, [
            'title' => 'New Task Name',
            'description' => 'New Task Description',
            'source_key' => 'New Source Key',
            'source_url' => 'https://example.com',
        ]);

        // Assert
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'New Task Name',
            'description' => 'New Task Description',
            'source_key' => 'New Source Key',
            'source_url' => 'https://example.com',
        ]);
    }

    public function test_it_can_update_the_state_of_a_task(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $task = Task::factory()->for($terminal)->create();
        $taskManagerService = new TaskManagementService($terminal);

        // Act
        $taskManagerService->updateTask($task, ['state' => TaskStateType::Blocked]);

        // Assert
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'state' => TaskStateType::Blocked]);
    }

    public function test_it_can_update_the_priority_of_a_task(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $task = Task::factory()->for($terminal)->create();
        $taskManagerService = new TaskManagementService($terminal);

        // Act
        $taskManagerService->updateTask($task, ['priority' => TaskPriorityType::High]);

        // Assert
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'priority' => TaskPriorityType::High]);
    }

    public function test_it_cannot_update_state_of_a_task_when_in_final_state(): void
    {
        // Expect
        $this->expectException(ModelNotModifiableException::class);

        // Arrange
        $task = Task::factory()->create(['state' => TaskStateType::Completed]);
        $taskManagerService = new TaskManagementService($task->terminal);

        // Act
        $taskManagerService->updateTask($task, ['state' => TaskStateType::Blocked]);
    }

    public function test_it_cannot_nullify_the_state_of_a_task(): void
    {
        // Expect
        $this->expectException(ModelFieldNotNullableException::class);

        // Arrange
        $task = Task::factory()->create(['state' => TaskStateType::Started]);
        $taskManagerService = new TaskManagementService($task->terminal);

        // Act
        $taskManagerService->updateTask($task, ['state' => null]);
    }

    public function test_it_cannot_nullify_the_priority_of_a_task(): void
    {
        // Expect
        $this->expectException(ModelFieldNotNullableException::class);

        // Arrange
        $task = Task::factory()->create(['priority' => TaskPriorityType::High]);
        $taskManagerService = new TaskManagementService($task->terminal);

        // Act
        $taskManagerService->updateTask($task, ['priority' => null]);
    }

    public function test_it_can_delete_a_task(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $task = Task::factory()->for($terminal)->create();
        $taskManagerService = new TaskManagementService($terminal);

        // Act
        $taskManagerService->deleteTask($task);

        // Assert
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_it_can_stop_all_active_tasks(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $task = Task::factory()->for($terminal)->create(['state' => TaskStateType::Started]);
        $taskManagerService = new TaskManagementService($terminal);

        // Act
        $taskManagerService->stopAllActiveTasks();

        // Assert
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'state' => TaskStateType::Stopped]);
    }

    public function test_it_updates_time_spent_on_tasks_when_stopping_all_active_tasks(): void
    {
        Carbon::setTestNow(now());

        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $task = Task::factory()->for($terminal)->create(['state' => TaskStateType::Started]);

        $worklog = Worklog::factory()->for($task)->create([
            'session_id' => $session->id,
            'started_at' => now()->subHour(),
            'duration' => 0,
        ]);

        $taskManagerService = new TaskManagementService($terminal);

        // Act
        $taskManagerService->stopAllActiveTasks();

        // Assert
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'total_minutes_spent' => 60]);
    }

    public function test_it_can_switch_from_one_active_task_to_an_inactive_task(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $activeTask = Task::factory()->for($terminal)->create(['state' => TaskStateType::Started]);
        $inactiveTask = Task::factory()->for($terminal)->create(['state' => TaskStateType::Stopped]);
        $taskManagerService = new TaskManagementService($terminal);

        // Act
        $taskManagerService->switchActiveTask($activeTask, $inactiveTask);

        // Assert
        $this->assertDatabaseHas('tasks', ['id' => $activeTask->id, 'state' => TaskStateType::Stopped]);
        $this->assertDatabaseHas('tasks', ['id' => $inactiveTask->id, 'state' => TaskStateType::Started]);
    }
}
