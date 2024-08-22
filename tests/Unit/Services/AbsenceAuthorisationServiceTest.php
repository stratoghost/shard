<?php

namespace Tests\Unit\Services;

use App\Exceptions\Absences\AbsenceDurationBeyondAllowedDurationException;
use App\Models\Terminal;
use App\Services\AbsenceAuthorisationService;
use App\SessionType;
use Tests\TestCase;

class AbsenceAuthorisationServiceTest extends TestCase
{
    public function test_it_can_mark_an_absence_as_authorised(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $absenceManagerService = new AbsenceAuthorisationService($terminal);
        $absence = $terminal->absences()->create([
            'date' => now(),
            'minutes_absent' => SessionType::StandardDuration->expectedDuration(),
        ]);

        // Act
        $absenceManagerService->authoriseAbsence($absence);

        // Assert
        $this->assertDatabaseHas('absences', [
            'id' => $absence->id,
            'terminal_id' => $terminal->id,
            'authorised_at' => now(),
        ]);
    }

    public function test_it_can_mark_an_absence_as_unauthorised(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $absenceManagerService = new AbsenceAuthorisationService($terminal);
        $absence = $terminal->absences()->create([
            'date' => now(),
            'minutes_absent' => SessionType::StandardDuration->expectedDuration(),
            'authorised_at' => now(),
        ]);

        // Act
        $absenceManagerService->rescindAbsenceAuthorisation($absence);

        // Assert
        $this->assertDatabaseHas('absences', [
            'id' => $absence->id,
            'authorised_at' => null,
        ]);
    }

    public function test_it_can_update_the_absence_minutes_taken(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $absenceManagerService = new AbsenceAuthorisationService($terminal);
        $absence = $terminal->absences()->create([
            'date' => now(),
            'minutes_absent' => SessionType::StandardDuration->expectedDuration(),
        ]);

        // Act
        $absenceManagerService->updateAbsenceMinutesTaken($absence, 200);

        // Assert
        $this->assertDatabaseHas('absences', [
            'id' => $absence->id,
            'minutes_absent' => 200,
        ]);
    }

    public function test_it_cannot_update_the_absence_minutes_taken_beyond_the_allowed_duration(): void
    {
        // Expect
        $this->expectException(AbsenceDurationBeyondAllowedDurationException::class);

        // Arrange
        $terminal = Terminal::factory()->create();
        $absenceManagerService = new AbsenceAuthorisationService($terminal);
        $absence = $terminal->absences()->create([
            'date' => now(),
            'minutes_absent' => SessionType::StandardDuration->expectedDuration(),
        ]);

        // Act
        $absenceManagerService->updateAbsenceMinutesTaken($absence, 1000);
    }
}
