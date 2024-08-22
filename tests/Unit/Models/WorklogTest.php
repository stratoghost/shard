<?php

namespace Tests\Unit\Models;

use App\Models\Session;
use App\Models\Task;
use App\Models\Worklog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorklogTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_model(): void
    {
        // Arrange
        $worklog = Worklog::factory()->make();

        // Act
        $worklog->save();

        // Assert
        $this->assertDatabaseHas('worklogs', ['id' => $worklog->id]);
    }

    public function test_it_returns_a_session(): void
    {
        // Arrange
        $session = Session::factory()->create();
        $worklog = Worklog::factory()->create([
            'session_id' => $session->id,
        ]);

        // Act
        $session = $worklog->session;

        // Assert
        $this->assertNotNull($session);
        $this->assertEquals($worklog->session_id, $session->id);
    }

    public function test_it_returns_a_task(): void
    {
        // Arrange
        $task = Task::factory()->create();
        $worklog = Worklog::factory()->create([
            'task_id' => $task->id,
        ]);

        // Act
        $task = $worklog->task;

        // Assert
        $this->assertEquals($worklog->task_id, $task->id);
    }
}
