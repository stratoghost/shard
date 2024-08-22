<?php

namespace Tests\Unit\Enums;

use App\TaskStateType;
use Tests\TestCase;

class TaskStateTypeTest extends TestCase
{
    public function test_it_returns_state_types_which_are_final(): void
    {
        // Arrange
        $expected = [
            TaskStateType::Cancelled,
            TaskStateType::Completed,
        ];

        // Assert
        $this->assertEquals($expected, TaskStateType::finalStates());
    }

    public function test_it_returns_state_types_which_are_not_final(): void
    {
        // Arrange
        $expected = [
            TaskStateType::Pending,
            TaskStateType::Blocked,
            TaskStateType::Stopped,
            TaskStateType::Started,
        ];

        // Assert
        $this->assertEquals($expected, TaskStateType::nonFinalStates());
    }

    public function test_it_returns_whether_a_state_type_is_final(): void
    {
        // Assert
        $this->assertTrue(TaskStateType::Cancelled->isFinal());
    }

    public function test_it_returns_state_types_which_are_inactive(): void
    {
        // Arrange
        $expected = [
            TaskStateType::Pending,
            TaskStateType::Stopped,
            TaskStateType::Blocked,
            TaskStateType::Cancelled,
            TaskStateType::Completed,
        ];

        // Assert
        $this->assertEquals($expected, TaskStateType::inactiveStates());
    }

    public function test_it_returns_state_types_which_are_active(): void
    {
        // Arrange
        $expected = [
            TaskStateType::Started,
        ];

        // Assert
        $this->assertEquals($expected, TaskStateType::activeStates());
    }

    public function test_it_returns_whether_a_state_type_is_active(): void
    {
        // Assert
        $this->assertTrue(TaskStateType::Started->isActive());
        $this->assertFalse(TaskStateType::Stopped->isActive());
    }
}
