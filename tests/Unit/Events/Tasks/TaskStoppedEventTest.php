<?php

namespace Tests\Unit\Events\Tasks;

use App\Events\Tasks\TaskStoppedEvent;
use App\Models\Task;
use App\TaskStateType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TaskStoppedEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_be_dispatched()
    {
        // Fake
        Event::fake([
            TaskStoppedEvent::class,
        ]);

        // Arrange
        $task = Task::factory()->create([
            'state' => TaskStateType::Stopped,
        ]);

        // Act
        TaskStoppedEvent::dispatch($task);

        // Assert
        Event::assertDispatched(TaskStoppedEvent::class, function ($event) use ($task) {
            return $event->task->is($task);
        });
    }
}
