<?php

namespace Tests\Unit\Services;

use App\Events\Terminals\TerminalStateChangedEvent;
use App\Models\Terminal;
use App\Services\TerminalStateManagerService;
use App\TerminalStateType;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TerminalStateManagerServiceTest extends TestCase
{
    public function test_it_can_switch_to_a_state_for_a_terminal(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $stateSwitchingService = new TerminalStateManagerService($terminal);

        // Act
        $stateSwitchingService->activateState(TerminalStateType::Busy);

        // Assert
        $this->assertDatabaseHas('terminals', [
            'id' => $terminal->id,
            'state' => TerminalStateType::Busy,
        ]);
    }

    public function test_it_dispatches_terminal_state_switched_event_when_changed(): void
    {
        // Expect
        Event::fake([
            TerminalStateChangedEvent::class,
        ]);

        // Arrange
        $terminal = Terminal::factory()->create();
        $stateSwitchingService = new TerminalStateManagerService($terminal);

        // Act
        $stateSwitchingService->activateState(TerminalStateType::Busy);

        // Assert
        Event::assertDispatched(TerminalStateChangedEvent::class, function ($event) use ($terminal) {
            return $event->terminal->is($terminal);
        });
    }

    public function test_it_does_not_dispatch_terminal_state_switched_event_when_updating_to_the_same_state()
    {
        Event::fake([
            TerminalStateChangedEvent::class,
        ]);

        // Arrange
        $terminal = Terminal::factory()->create([
            'state' => TerminalStateType::Busy,
        ]);

        $stateSwitchingService = new TerminalStateManagerService($terminal);

        // Act
        $stateSwitchingService->activateState(TerminalStateType::Busy);

        // Assert
        Event::assertNothingDispatched();
    }
}
