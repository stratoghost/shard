<?php

namespace Tests\Unit\Enums;

use App\TaskSourceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskSourceTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_gets_default_source(): void
    {
        // Act
        $defaultSource = TaskSourceType::default();

        // Assert
        $this->assertEquals(TaskSourceType::Internal, $defaultSource);
    }
}
