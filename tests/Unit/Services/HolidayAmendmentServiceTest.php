<?php

namespace Tests\Unit\Services;

use App\Models\Terminal;
use App\Services\HolidayAmendmentService;
use App\SessionType;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class HolidayAmendmentServiceTest extends TestCase
{
    public function test_it_can_cancel_a_holiday(): void
    {
        // Arrange
        Carbon::setTestNow(now());
        $terminal = Terminal::factory()->create();
        $holidayManagerService = new HolidayAmendmentService($terminal);
        $holiday = $terminal->holidays()->create([
            'date' => now(),
            'minutes_authorised' => SessionType::StandardDuration->expectedDuration(),
        ]);

        // Act
        $holidayManagerService->cancelHoliday($holiday);

        // Assert
        $this->assertDatabaseHas('holidays', [
            'id' => $holiday->id,
            'cancelled_at' => now(),
        ]);
    }

    public function test_it_can_adjust_the_authorised_minutes_of_a_holiday(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $holidayManagerService = new HolidayAmendmentService($terminal);
        $holiday = $terminal->holidays()->create([
            'date' => now(),
            'minutes_authorised' => SessionType::StandardDuration->expectedDuration(),
        ]);

        // Act
        $holidayManagerService->changeAuthorisedMinutes($holiday, 480);

        // Assert
        $this->assertDatabaseHas('holidays', [
            'id' => $holiday->id,
            'minutes_authorised' => 480,
        ]);
    }
}
