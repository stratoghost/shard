<?php

namespace Tests\Unit\Enums;

use App\TaskQueueType;
use Tests\TestCase;

class TaskQueueTypeTest extends TestCase
{
    public function test_it_returns_task_queue_cases(): void
    {
        // Act
        $cases = TaskQueueType::cases();

        // Assert
        $this->assertEquals([
            TaskQueueType::Incident,
            TaskQueueType::Assistance,
            TaskQueueType::ServiceDesk,
            TaskQueueType::Scheduled,
            TaskQueueType::Unscheduled,
        ], $cases);
    }

    public function test_it_returns_default_type(): void
    {
        // Arrange
        $defaultType = TaskQueueType::default();

        // Assert
        $this->assertEquals(TaskQueueType::Unscheduled, $defaultType);
    }

    public function test_it_returns_queue_labels(): void
    {
        // Act
        $labels = TaskQueueType::labels();

        // Assert
        $this->assertEquals([
            TaskQueueType::Incident->value => 'Incident',
            TaskQueueType::Assistance->value => 'Assistance',
            TaskQueueType::ServiceDesk->value => 'Service Desk',
            TaskQueueType::Scheduled->value => 'Scheduled',
            TaskQueueType::Unscheduled->value => 'Unscheduled',
        ], $labels);
    }
}
