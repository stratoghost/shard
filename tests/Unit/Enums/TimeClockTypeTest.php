<?php

namespace Tests\Unit\Enums;

use App\TimeClockType;
use Tests\TestCase;

class TimeClockTypeTest extends TestCase
{
    public function test_it_returns_default_type(): void
    {
        // Arrange
        $defaultType = TimeClockType::default();

        // Assert
        $this->assertEquals(TimeClockType::OnDuty, $defaultType);
    }
}
