<?php

namespace Tests\Unit\Services;

use App\Exceptions\Common\DuplicateModelException;
use App\Exceptions\Holidays\HolidayDurationBeyondAllowedDurationException;
use App\Models\Terminal;
use App\Services\HolidayBookingService;
use App\SessionType;
use Tests\TestCase;

class HolidayBookingServiceTest extends TestCase
{
    public function test_it_can_add_a_holiday(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $holidayManagerService = new HolidayBookingService($terminal);

        // Act
        $holidayManagerService->addHoliday(now());

        // Assert
        $this->assertDatabaseHas('holidays', [
            'terminal_id' => $terminal->id,
            'date' => now(),
            'minutes_authorised' => SessionType::StandardDuration->expectedDuration(),
        ]);
    }

    public function test_it_cannot_add_a_holiday_on_the_same_date_as_an_existing_holiday(): void
    {
        // Expect
        $this->expectException(DuplicateModelException::class);

        // Arrange
        $terminal = Terminal::factory()->create();
        $holidayManagerService = new HolidayBookingService($terminal);
        $terminal->holidays()->create([
            'date' => now(),
            'minutes_authorised' => SessionType::StandardDuration->expectedDuration(),
        ]);

        // Act
        $holidayManagerService->addHoliday(now());
    }

    public function test_it_can_add_holiday_with_custom_authorised_minutes(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $holidayManagerService = new HolidayBookingService($terminal);

        // Act
        $holidayManagerService->addHoliday(now(), 250);

        // Assert
        $this->assertDatabaseHas('holidays', [
            'terminal_id' => $terminal->id,
            'date' => now(),
            'minutes_authorised' => 250,
        ]);
    }

    public function test_it_cannot_add_a_holiday_with_authorised_minutes_longer_than_standard_duration(): void
    {
        // Expect
        $this->expectException(HolidayDurationBeyondAllowedDurationException::class);

        // Arrange
        $terminal = Terminal::factory()->create();
        $holidayManagerService = new HolidayBookingService($terminal);

        // Act
        $holidayManagerService->addHoliday(now(), 500);
    }
}
