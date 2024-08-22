<?php

namespace Tests\Unit\Observers;

use App\AbsenceType;
use App\Models\Absence;
use App\Models\Terminal;
use App\SessionType;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AbsenceTest extends TestCase
{
    public function test_it_sets_default_minutes_absent_when_none_provided(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $holiday = new Absence(['date' => now(), 'terminal_id' => $terminal->id, 'authorised_at' => now()]);

        // Act
        $holiday->save();

        // Assert
        $this->assertEquals(SessionType::StandardDuration->expectedDuration(), $holiday->minutes_absent);
        $this->assertEquals($terminal->id, $holiday->terminal_id);
    }

    public function test_it_sets_authorised_at_to_now_when_not_provided(): void
    {
        // Arrange
        Carbon::setTestNow(now());
        $terminal = Terminal::factory()->create();
        $holiday = new Absence(['date' => now(), 'terminal_id' => $terminal->id]);

        // Act
        $holiday->save();

        // Assert
        $this->assertEquals(now()->format('Y-m-d H:i:s'), $holiday->authorised_at->format('Y-m-d H:i:s'));
        $this->assertEquals($terminal->id, $holiday->terminal_id);
    }

    public function test_it_sets_default_absence_type_when_none_provided(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $holiday = new Absence(['date' => now(), 'terminal_id' => $terminal->id, 'authorised_at' => now()]);

        // Act
        $holiday->save();

        // Assert
        $this->assertEquals(AbsenceType::UnpaidLeave, $holiday->type);
        $this->assertEquals($terminal->id, $holiday->terminal_id);
    }
}
