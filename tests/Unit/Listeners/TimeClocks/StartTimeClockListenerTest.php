<?php

namespace Tests\Unit\Listeners\TimeClocks;

use App\Events\Terminals\TerminalStateChangedEvent;
use App\Listeners\TimeClocks\StartTimeClockListener;
use App\Models\Session;
use App\Models\Terminal;
use App\Models\TimeClock;
use App\TerminalStateType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StartTimeClockListenerTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('providesUnavailableTerminalStates')]
    public function test_it_does_not_start_time_clock_when_on_duty_terminal_state_given_and_no_session_activated(array $providerAttributes)
    {
        // Provider
        [
            'terminalStateType' => $terminalStateType
        ] = $providerAttributes;

        // Arrange
        $count = TimeClock::count();

        $terminal = Terminal::factory()->create([
            'state' => $terminalStateType,
        ]);

        $terminalStateChangedEvent = new TerminalStateChangedEvent($terminal);
        $startTimeClockListener = new StartTimeClockListener($terminalStateChangedEvent);

        // Act
        $startTimeClockListener->handle($terminalStateChangedEvent);

        // Assert
        $this->assertDatabaseHas('terminals', [
            'id' => $terminal->id,
            'state' => $terminalStateType->value,
        ]);

        $this->assertDatabaseCount('time_clocks', $count);
    }

    #[DataProvider('providesOnDutyTerminalStates')]
    public function test_it_starts_time_clock_when_on_duty_terminal_state_passed(array $providerAttributes)
    {
        // Provider
        [
            'terminalStateType' => $terminalStateType
        ] = $providerAttributes;

        // Arrange
        $count = TimeClock::count();

        $terminal = Terminal::factory()->create([
            'state' => $terminalStateType,
        ]);

        $session = Session::factory()->for($terminal)->create();
        $terminalStateChangedEvent = new TerminalStateChangedEvent($terminal);
        $startTimeClockListener = new StartTimeClockListener($terminalStateChangedEvent);

        // Act
        $startTimeClockListener->handle($terminalStateChangedEvent);

        // Assert
        $this->assertDatabaseHas('time_clocks', [
            'session_id' => $session->id,
            'started_at' => now(),
            'ended_at' => null,
        ]);

        $this->assertDatabaseCount('time_clocks', $count + 1);
    }

    #[DataProvider('providesOffDutyTerminalStates')]
    public function test_it_starts_time_clock_when_off_duty_terminal_state_passed(array $providerAttributes)
    {
        // Provider
        [
            'terminalStateType' => $terminalStateType
        ] = $providerAttributes;

        // Arrange
        $count = TimeClock::count();

        $terminal = Terminal::factory()->create([
            'state' => $terminalStateType,
        ]);

        $session = Session::factory()->for($terminal)->create();
        $terminalStateChangedEvent = new TerminalStateChangedEvent($terminal);
        $startTimeClockListener = new StartTimeClockListener($terminalStateChangedEvent);

        // Act
        $startTimeClockListener->handle($terminalStateChangedEvent);

        // Assert
        $this->assertDatabaseHas('time_clocks', [
            'session_id' => $session->id,
            'started_at' => now(),
            'ended_at' => null,
        ]);

        $this->assertDatabaseCount('time_clocks', $count + 1);
    }

    #[DataProvider('providesUnavailableTerminalStates')]
    public function test_it_does_not_start_time_clock_when_unavailable_terminal_state_passed(array $providerAttributes)
    {
        // Provider
        [
            'terminalStateType' => $terminalStateType
        ] = $providerAttributes;

        // Arrange
        $count = TimeClock::count();

        $terminal = Terminal::factory()->create([
            'state' => $terminalStateType,
        ]);

        $session = Session::factory()->for($terminal)->create();
        $terminalStateChangedEvent = new TerminalStateChangedEvent($terminal);
        $startTimeClockListener = new StartTimeClockListener($terminalStateChangedEvent);

        // Act
        $startTimeClockListener->handle($terminalStateChangedEvent);

        // Assert
        $this->assertDatabaseMissing('time_clocks', [
            'session_id' => $session->id,
        ]);

        $this->assertDatabaseCount('time_clocks', $count);
    }

    public function test_it_does_not_start_the_same_time_clock(): void
    {
        // Arrange
        $count = TimeClock::count();

        $terminal = Terminal::factory()->create([
            'state' => TerminalStateType::Busy,
        ]);

        $session = Session::factory()->for($terminal)->create();

        $terminalStateChangedEvent = new TerminalStateChangedEvent($terminal);
        $startTimeClockListener = new StartTimeClockListener($terminalStateChangedEvent);

        $startTimeClockListener->handle($terminalStateChangedEvent);

        $terminal->update([
            'state' => TerminalStateType::Available,
        ]);

        // Act
        $terminalStateChangedEvent = new TerminalStateChangedEvent($terminal);
        $startTimeClockListener = new StartTimeClockListener($terminalStateChangedEvent);

        $startTimeClockListener->handle($terminalStateChangedEvent);

        // Assert
        $this->assertDatabaseCount('time_clocks', $count + 1);
    }

    public static function providesOnDutyTerminalStates(): array
    {
        return [
            [
                ['terminalStateType' => TerminalStateType::Busy],
            ],
            [
                ['terminalStateType' => TerminalStateType::Available],
            ],
            [
                ['terminalStateType' => TerminalStateType::Meeting],
            ],
            [
                ['terminalStateType' => TerminalStateType::Incident],
            ],
        ];
    }

    public static function providesOffDutyTerminalStates(): array
    {
        return [
            [
                ['terminalStateType' => TerminalStateType::Break],
            ],
        ];
    }

    public static function providesUnavailableTerminalStates(): array
    {
        return [
            [
                ['terminalStateType' => TerminalStateType::Unavailable],
            ],
            [
                ['terminalStateType' => TerminalStateType::Holiday],
            ],
        ];
    }
}
