<?php

namespace Tests\Unit\Observers;

use App\Models\Terminal;
use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_it_creates_a_terminal_after_creating_user()
    {
        // Act
        $user = User::factory()->create();

        // Assert
        $this->assertNotEmpty($user->terminals);
        $this->assertInstanceOf(Terminal::class, $user->terminals->first());
    }
}
