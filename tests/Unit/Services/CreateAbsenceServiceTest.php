<?php

namespace Tests\Unit\Services;

use App\AbsenceType;
use App\Models\Terminal;
use App\Services\CreateAbsenceService;
use App\SessionType;
use Tests\TestCase;

class CreateAbsenceServiceTest extends TestCase
{
    public function test_it_can_create_an_absence(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $absenceManagerService = new CreateAbsenceService($terminal);

        // Act
        $absenceManagerService->addAbsence(now(), AbsenceType::UnpaidLeave);

        // Assert
        $this->assertDatabaseHas('absences', [
            'terminal_id' => $terminal->id,
            'date' => now()->toDateString(),
            'minutes_absent' => SessionType::StandardDuration->expectedDuration(),
        ]);
    }

    public function test_it_can_create_an_absence_with_custom_minutes_taken(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $absenceManagerService = new CreateAbsenceService($terminal);

        // Act
        $absenceManagerService->addAbsence(now(), AbsenceType::Bereavement, 200);

        // Assert
        $this->assertDatabaseHas('absences', [
            'terminal_id' => $terminal->id,
            'date' => now()->toDateString(),
            'minutes_absent' => 200,
            'type' => AbsenceType::Bereavement,
        ]);
    }
}
