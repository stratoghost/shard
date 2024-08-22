<?php

namespace Tests\Unit\Enums;

use App\AbsenceType;
use Tests\TestCase;

class AbsenceTypeTest extends TestCase
{
    public function test_it_returns_default_type(): void
    {
        // Arrange
        $defaultType = AbsenceType::default();

        // Assert
        $this->assertEquals(AbsenceType::UnpaidLeave, $defaultType);
    }
}
