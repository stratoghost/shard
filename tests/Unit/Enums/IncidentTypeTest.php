<?php

namespace Tests\Unit\Enums;

use App\IncidentType;
use Tests\TestCase;

class IncidentTypeTest extends TestCase
{
    public function test_it_returns_default_type(): void
    {
        // Arrange
        $defaultType = IncidentType::default();

        // Assert
        $this->assertEquals(IncidentType::Incident, $defaultType);
    }
}
