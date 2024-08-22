<?php

namespace Tests\Unit\Enums;

use App\TerminalStateType;
use App\TimeClockType;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TerminalStateTypeTest extends TestCase
{
    public function test_it_returns_default_type(): void
    {
        // Arrange
        $defaultType = TerminalStateType::default();

        // Assert
        $this->assertEquals(TerminalStateType::Available, $defaultType);
    }

    #[DataProvider('providesOnDutyTerminalStates')]
    public function test_it_returns_off_duty_time_clock_for_unavailable_states(array $providerAttributes): void
    {
        // Provider
        [
            'terminalStateType' => $terminalStateType
        ] = $providerAttributes;

        // Act
        $result = $terminalStateType->timeClockType();

        // Assert
        $this->assertEquals(TimeClockType::OnDuty, $result);
    }

    #[DataProvider('providesOffDutyTerminalStates')]
    public function test_it_returns_on_duty_time_clocks_for_available_states(array $providerAttributes): void
    {
        // Provider
        [
            'terminalStateType' => $terminalStateType
        ] = $providerAttributes;

        // Act
        $result = $terminalStateType->timeClockType();

        // Assert
        $this->assertEquals(TimeClockType::OffDuty, $result);
    }

    public function test_it_returns_active_terminal_states(): void
    {
        // Act
        $terminalStateTypes = TerminalStateType::trackableStates();

        // Assert
        $this->assertEquals([
            TerminalStateType::Available,
            TerminalStateType::Busy,
            TerminalStateType::Meeting,
            TerminalStateType::Incident,
            TerminalStateType::Break,
        ], $terminalStateTypes);
    }

    public function test_it_returns_non_working_states(): void
    {
        // Act
        $terminalStateTypes = TerminalStateType::nonWorkingStates();

        // Assert
        $this->assertEquals([
            TerminalStateType::Meeting,
            TerminalStateType::Unavailable,
            TerminalStateType::Holiday,
            TerminalStateType::Incident,
            TerminalStateType::Break,
        ], $terminalStateTypes);
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
            [
                ['terminalStateType' => TerminalStateType::Unavailable],
            ],
            [
                ['terminalStateType' => TerminalStateType::Holiday],
            ],
        ];
    }
}
