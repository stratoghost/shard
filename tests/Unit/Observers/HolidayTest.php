<?php

namespace Tests\Unit\Observers;

use App\Models\Holiday;
use App\Models\Terminal;
use App\SessionType;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class HolidayTest extends TestCase
{
    public function test_it_sets_default_authorised_minutes_when_none_provided(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $holiday = new Holiday(['date' => now(), 'terminal_id' => $terminal->id, 'authorised_at' => now()]);

        // Act
        $holiday->save();

        // Assert
        $this->assertEquals(SessionType::StandardDuration->expectedDuration(), $holiday->minutes_authorised);
        $this->assertTrue($holiday->terminal->is($terminal));
    }

    public function test_it_sets_authorised_at_to_now_when_not_provided(): void
    {
        // Arrange
        Carbon::setTestNow(now());
        $terminal = Terminal::factory()->create();
        $holiday = new Holiday(['date' => now(), 'terminal_id' => $terminal->id]);

        // Act
        $holiday->save();

        // Assert
        $this->assertEquals(now()->format('Y-m-d H:i:s'), $holiday->authorised_at->format('Y-m-d H:i:s'));
        $this->assertTrue($holiday->terminal->is($terminal));
    }

    public function test_it_defaults_holiday_date_when_none_provided(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $holiday = new Holiday(['terminal_id' => $terminal->id]);

        // Act
        $holiday->save();

        // Assert
        $this->assertEquals(now()->format('Y-m-d'), $holiday->date->format('Y-m-d'));
        $this->assertTrue($holiday->terminal->is($terminal));
    }
}
