<?php

namespace Tests\Unit\Models;

use App\Models\Attachment;
use App\Models\Task;
use App\Models\Terminal;
use App\Models\Trace;
use App\Models\Worklog;
use App\TaskPriorityType;
use App\TaskQueueType;
use App\TaskSourceType;
use App\TaskStateType;
use App\TraceLinkType;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TaskTest extends TestCase
{
    public function test_it_creates_a_model(): void
    {
        // Arrange
        $task = Task::factory()->make();

        // Act
        $task->save();

        // Assert
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_it_returns_a_terminal(): void
    {
        // Arrange
        $task = Task::factory()->create();

        // Act
        $terminal = $task->terminal;

        // Assert
        $this->assertInstanceOf(Terminal::class, $terminal);
        $this->assertEquals($task->terminal_id, $terminal->id);
    }

    public function test_it_casts_state_to_enum(): void
    {
        // Arrange
        $task = Task::factory()->make(['state' => TaskStateType::Blocked]);

        // Act
        $task->save();

        // Assert
        $this->assertEquals(TaskStateType::Blocked, $task->state);
    }

    public function test_it_casts_priority_to_enum(): void
    {
        // Arrange
        $task = Task::factory()->make(['priority' => TaskPriorityType::High]);

        // Act
        $task->save();

        // Assert
        $this->assertEquals(TaskPriorityType::High, $task->priority);
    }

    public function test_it_cases_source_to_enum(): void
    {
        // Arrange
        $task = Task::factory()->make(['source' => TaskSourceType::Teams]);

        // Act
        $task->save();

        // Assert
        $this->assertEquals(TaskSourceType::Teams, $task->source);
    }

    public function test_it_returns_traces(): void
    {
        // Arrange
        $task = Task::factory()->create();
        $trace = Trace::factory()->for($task->terminal)->create([
            'traceable_id' => $task->id,
            'traceable_type' => Task::class,
        ]);

        // Act
        $traces = $task->traces;

        // Assert
        $this->assertCount(1, $traces);
        $this->assertTrue($traces->contains($trace));

        $this->assertEquals($trace->traceable_id, $task->id);
        $this->assertEquals($trace->terminal_id, $task->terminal_id);
    }

    public function test_it_returns_linkable_traces(): void
    {
        // Arrange
        $task = Task::factory()->create();
        $trace = Trace::factory()->for($task->terminal)->create([
            'traceable_id' => $task->id,
            'traceable_type' => Task::class,
        ]);

        $task->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $task->links;

        // Assert
        $this->assertCount(1, $linkableTraces);

        $this->assertTrue($linkableTraces->contains($trace));

        $this->assertEquals($trace->traceable_id, $task->id);
        $this->assertEquals($trace->terminal_id, $task->terminal_id);
    }

    public function test_it_returns_linkable_traces_with_link_type(): void
    {
        // Arrange
        $task = Task::factory()->create();
        $trace = Trace::factory()->for($task->terminal)->create([
            'traceable_id' => $task->id,
            'traceable_type' => Task::class,
        ]);

        $task->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $task->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertEquals(TraceLinkType::Related->value, $linkableTraces->first()->pivot->trace_link_type);

        $this->assertTrue($linkableTraces->contains($trace));

        $this->assertEquals($trace->traceable_id, $task->id);
        $this->assertEquals($trace->terminal_id, $task->terminal_id);
    }

    public function test_it_returns_attachments(): void
    {
        // Arrange
        $task = Task::factory()->create();

        $attachment = Attachment::factory()->create([
            'terminal_id' => $task->terminal_id,
            'attachable_id' => $task->id,
            'attachable_type' => Task::class,
        ]);

        // Act
        $attachments = $task->attachments;

        // Assert
        $this->assertCount(1, $attachments);

        $this->assertTrue($attachments->contains($attachment));

        $this->assertEquals($task->id, $attachments->first()->attachable->id);
        $this->assertEquals($task->terminal_id, $attachments->first()->terminal_id);
    }

    public function test_it_casts_queue_to_enum(): void
    {
        // Arrange
        $task = Task::factory()->make(['queue' => TaskQueueType::Unscheduled]);

        // Act
        $task->save();

        // Assert
        $this->assertEquals(TaskQueueType::Unscheduled, $task->queue);
    }

    public function test_it_returns_all_open_tasks_through_scope(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        Task::factory(3)->for($terminal)->create(['state' => TaskStateType::Pending]);

        // Act
        $openTasks = Task::notClosed()
            ->where('terminal_id', $terminal->id)
            ->get();

        // Assert
        $this->assertCount(3, $openTasks);
    }

    public function test_it_returns_all_closed_tasks_through_scope(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        Task::factory(3)->for($terminal)->create(['state' => TaskStateType::Completed]);

        // Act
        $closedTasks = Task::closed()
            ->where('terminal_id', $terminal->id)
            ->get();

        // Assert
        $this->assertCount(3, $closedTasks);
    }

    public function test_it_returns_all_open_tasks_excludes_closed_tasks_through_scope(): void
    {
        Carbon::setTestNow(now());

        // Arrange
        $terminal = Terminal::factory()->create();
        Task::factory(3)->for($terminal)->create(['state' => TaskStateType::Completed]);
        Task::factory(2)->for($terminal)->create(['state' => TaskStateType::Pending]);

        // Act
        $openTasks = Task::notClosed()
            ->where('terminal_id', $terminal->id)
            ->get();

        // Assert
        $this->assertCount(2, $openTasks);
    }

    public function test_it_returns_parent_task(): void
    {
        // Arrange
        $parentTask = Task::factory()->create();
        $task = Task::factory()->create(['parent_id' => $parentTask->id]);

        // Act
        $parent = $task->parent;

        // Assert
        $this->assertInstanceOf(Task::class, $parent);
        $this->assertEquals($parentTask->id, $parent->id);
    }

    public function test_it_returns_child_tasks(): void
    {
        // Arrange
        $parentTask = Task::factory()->create();
        $childTasks = Task::factory(3)->create(['parent_id' => $parentTask->id]);

        // Act
        $children = $parentTask->children;

        // Assert
        $this->assertCount(3, $children);
        $this->assertTrue($children->contains($childTasks->first()));
    }

    public function test_it_returns_worklogs(): void
    {
        // Arrange
        $task = Task::factory()->create();
        $worklog = Worklog::factory()->create(['task_id' => $task->id]);

        // Act
        $worklogs = $task->worklogs;

        // Assert
        $this->assertCount(1, $worklogs);
        $this->assertTrue($worklogs->contains($worklog));
    }
}
