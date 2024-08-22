<?php

namespace Tests\Unit\Listeners\Tasks;

use App\Events\Sessions\SessionEndedEvent;
use App\Listeners\Tasks\StopActiveTasksWhenSessionEndedListener;
use App\Models\Session;
use App\Models\Task;
use App\Models\Terminal;
use App\TaskStateType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StopActiveTasksWhenSessionEndedListenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_ends_active_tasks_when_session_ended(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $task = Task::factory()->for($terminal)->create(['state' => TaskStateType::Started]);
        $sessionEndedEvent = new SessionEndedEvent($session);
        $stopActiveTasksListener = new StopActiveTasksWhenSessionEndedListener;

        // Act
        $stopActiveTasksListener->handle($sessionEndedEvent);

        // Assert
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'state' => TaskStateType::Stopped,
        ]);
    }
}
