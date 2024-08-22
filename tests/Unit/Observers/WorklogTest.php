<?php

namespace Tests\Unit\Observers;

use App\Models\Worklog;
use Carbon\Carbon;
use Tests\TestCase;

class WorklogTest extends TestCase
{
    public function test_it_sets_duration_to_zero_when_unset(): void
    {
        // Arrange
        $worklog = Worklog::factory()->make(['duration' => null]);

        // Act
        $worklog->save();

        // Assert
        $this->assertEquals(0, $worklog->duration);
    }

    public function test_it_sets_started_at_when_not_set(): void
    {
        // Arrange
        $worklog = Worklog::factory()->make(['started_at' => null]);

        // Act
        $worklog->save();

        // Assert
        $this->assertNotNull($worklog->started_at);
    }

    public function test_it_calculates_duration_when_worklog_ended_at_updated(): void
    {
        Carbon::setTestNow(now());

        // Arrange
        $worklog = Worklog::factory()->create([
            'started_at' => now()->subHour(),
            'duration' => 0,
        ]);

        // Act
        $worklog->ended_at = now();
        $worklog->save();

        // Assert
        $this->assertDatabaseHas('worklogs', [
            'id' => $worklog->id,
            'duration' => 60,
        ]);
    }

    public function test_it_updates_total_minutes_spent_on_parent_task_when_ended_at_updated(): void
    {
        Carbon::setTestNow(now());

        // Arrange
        $worklog = Worklog::factory()->create([
            'started_at' => now()->subHour(),
            'duration' => 0,
        ]);

        $worklog->ended_at = now();

        // Act
        $worklog->save();

        // Assert
        $this->assertDatabaseHas('tasks', [
            'id' => $worklog->task->id,
            'total_minutes_spent' => 60,
        ]);
    }
}
