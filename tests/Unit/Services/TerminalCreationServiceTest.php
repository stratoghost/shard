<?php

namespace Tests\Unit\Services;

use App\Exceptions\Common\DuplicateModelException;
use App\Exceptions\Terminals\EmptyTerminalIdentifierException;
use App\Models\Terminal;
use App\Models\User;
use App\Services\TerminalCreationService;
use Tests\TestCase;

class TerminalCreationServiceTest extends TestCase
{
    public function test_it_can_create_a_terminal(): void
    {
        // Arrange
        $user = User::withoutEvents(function () {
            return User::factory()->create();
        });

        $terminal = new TerminalCreationService($user);
        $identifier = 'JDU_123';

        // Act
        $result = $terminal->createTerminal($identifier);

        // Assert
        $this->assertInstanceOf(Terminal::class, $result);
        $this->assertTrue($result->exists);
        $this->assertDatabaseHas('terminals', [
            'identifier' => $identifier,
        ]);
    }

    public function test_it_cannot_create_terminal_with_existing_identifier(): void
    {
        // Expect
        $this->expectException(DuplicateModelException::class);

        // Arrange
        $user = User::withoutEvents(function () {
            return User::factory()->create();
        });

        $terminal = new TerminalCreationService($user);
        $identifier = 'JDU_123';
        $terminal->createTerminal($identifier);

        // Act
        $terminal->createTerminal($identifier);
    }

    public function test_it_cannot_create_terminal_with_empty_identifier(): void
    {
        // Expect
        $this->expectException(EmptyTerminalIdentifierException::class);

        // Arrange
        $user = User::withoutEvents(function () {
            return User::factory()->create();
        });

        $terminal = new TerminalCreationService($user);
        $identifier = '';

        // Act
        $terminal->createTerminal($identifier);
    }
}
