<?php

namespace Tests\Unit\Enums;

use App\TraceType;
use PHPUnit\Framework\TestCase;

class TraceTypeTest extends TestCase
{
    public function test_it_returns_default_type()
    {
        // Arrange
        $defaultType = TraceType::default();

        // Assert
        $this->assertEquals(TraceType::Normal, $defaultType);
    }

    public function test_it_returns_system_type()
    {
        // Arrange
        $systemType = TraceType::systemType();

        // Assert
        $this->assertEquals(TraceType::System, $systemType);
    }

    public function test_it_returns_logging_types()
    {
        // Arrange
        $loggingTypes = TraceType::loggingTypes();

        // Assert
        $this->assertEquals([
            TraceType::Event,
            TraceType::Alert,
            TraceType::Normal,
            TraceType::Recall,
        ], $loggingTypes);
    }

    public function test_it_returns_interaction_types()
    {
        // Arrange
        $interactionTypes = TraceType::interactionTypes();

        // Assert
        $this->assertEquals([
            TraceType::Communication,
            TraceType::Instruction,
            TraceType::Outcome,
        ], $interactionTypes);
    }
}
