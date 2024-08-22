<?php

namespace Tests\Unit\Enums;

use App\TaskPriorityType;
use Tests\TestCase;

class TaskPriorityTypeTest extends TestCase
{
    public function test_it_returns_default_type(): void
    {
        // Arrange
        $defaultType = TaskPriorityType::default();

        // Assert
        $this->assertEquals(TaskPriorityType::None, $defaultType);
    }
}
