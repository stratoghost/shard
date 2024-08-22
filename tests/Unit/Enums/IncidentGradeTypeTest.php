<?php

namespace Tests\Unit\Enums;

use App\IncidentGradeType;
use Tests\TestCase;

class IncidentGradeTypeTest extends TestCase
{
    public function test_it_returns_default_type(): void
    {
        // Arrange
        $defaultType = IncidentGradeType::default();

        // Assert
        $this->assertEquals(IncidentGradeType::Information, $defaultType);
    }
}
