<?php

namespace Tests\Unit\Enums;

use App\SessionType;
use Tests\TestCase;

class SessionTypeTest extends TestCase
{
    public function test_session_type_on_call_returns_zero_for_session_duration(): void
    {
        // Arrange
        $sessionType = SessionType::OnCall;

        // Act
        $sessionDuration = $sessionType->expectedDuration();

        // Assert
        $this->assertEquals(0, $sessionDuration);
    }

    public function test_session_type_standard_returns_expected_minutes(): void
    {
        // Arrange
        $sessionType = SessionType::StandardDuration;

        // Act
        $sessionDuration = $sessionType->expectedDuration();

        // Assert
        $this->assertEquals(420, $sessionDuration);
    }

    public function test_session_type_custom_returns_expected_minutes(): void
    {
        // Arrange
        $sessionType = SessionType::CustomDuration;

        // Act
        $sessionDuration = $sessionType->expectedDuration();

        // Assert
        $this->assertEquals(240, $sessionDuration);
    }

    public function test_session_type_standard_does_not_automatically_accrue_overtime_by_default(): void
    {
        // Arrange
        $sessionType = SessionType::StandardDuration;

        // Act
        $accruesOvertime = $sessionType->accruesOvertimeByDefault();

        // Assert
        $this->assertFalse($accruesOvertime);
    }

    public function test_session_type_custom_does_not_automatically_accrue_overtime_by_default(): void
    {
        // Arrange
        $sessionType = SessionType::CustomDuration;

        // Act
        $accruesOvertime = $sessionType->accruesOvertimeByDefault();

        // Assert
        $this->assertFalse($accruesOvertime);
    }

    public function test_session_type_on_call_does_automatically_accrue_overtime_by_default(): void
    {
        // Arrange
        $sessionType = SessionType::OnCall;

        // Act
        $accruesOvertime = $sessionType->accruesOvertimeByDefault();

        // Assert
        $this->assertTrue($accruesOvertime);
    }
}
