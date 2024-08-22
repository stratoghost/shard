<?php

namespace Tests\Unit\Events\Terminals;

use App\Events\Terminals\TerminalStateChangedEvent;
use App\Models\Terminal;
use App\TerminalStateType;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TerminalStateChangedEventTest extends TestCase
{
    public function test_it_can_be_dispatched()
    {
        // Fake
        Event::fake([
            TerminalStateChangedEvent::class,
        ]);

        // Arrange
        $terminal = Terminal::factory()->create([
            'state' => TerminalStateType::Available,
        ]);

        // Act
        TerminalStateChangedEvent::dispatch($terminal);

        // Assert
        Event::assertDispatched(TerminalStateChangedEvent::class, function ($event) use ($terminal) {
            return $event->terminal->is($terminal);
        });
    }
}
