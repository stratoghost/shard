<?php

namespace Tests\Unit\Enums;

use App\TraceLinkType;
use Tests\TestCase;

class TraceLinkTypeTest extends TestCase
{
    public function test_it_returns_default_link_type(): void
    {
        // Assert
        $this->assertSame(TraceLinkType::Related, TraceLinkType::default());
    }
}
