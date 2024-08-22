<?php

namespace Tests\Unit\Services;

use App\Models\Session;
use App\Models\Terminal;
use App\Models\TimeClock;
use App\Services\SessionTimeRemainingService;
use App\SessionType;
use App\TimeClockType;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SessionTimeRemainingServiceTest extends TestCase
{
    public function test_it_can_return_the_remaining_number_of_minutes_in_a_session()
    {
        Carbon::setTestNow(now()->setTime(15, 0));

        // Arrange
        $terminal = Terminal::factory()->create();

        $session = Session::factory()->for($terminal)->createQuietly([
            'started_at' => now()->setTime(9, 0),
            'expected_minutes' => SessionType::StandardDuration->expectedDuration(),
            'type' => SessionType::StandardDuration,
        ]);

        $timeClock = TimeClock::factory()->for($session)->create([
            'started_at' => now()->setTime(9, 0),
        ]);

        $timeClock->update([
            'ended_at' => now(),
        ]);

        $sessionTimeRemainingService = new SessionTimeRemainingService($session);

        // Act
        $remainingMinutes = $sessionTimeRemainingService->getPostCalculatedRemainingMinutes();

        // Assert
        $this->assertEquals(60, $remainingMinutes);
    }

    public function test_it_returns_negative_value_when_remaining_minutes_less_than_zero()
    {
        // Arrange
        $terminal = Terminal::factory()->create();

        $session = Session::factory()->for($terminal)->createQuietly([
            'started_at' => now()->subMinutes(90),
            'expected_minutes' => 60,
            'type' => SessionType::StandardDuration,
        ]);

        $timeClock = TimeClock::factory()->for($session)->create([
            'started_at' => now()->subMinutes(90),
        ]);

        $timeClock->update([
            'ended_at' => now(),
        ]);

        $sessionTimeRemainingService = new SessionTimeRemainingService($session);

        // Act
        $remainingMinutes = $sessionTimeRemainingService->getPostCalculatedRemainingMinutes();

        // Assert
        $this->assertEquals(-30, $remainingMinutes);
    }

    public function test_it_returns_remaining_live_minutes_without_time_clock_in_progress()
    {
        Carbon::setTestNow(now()->setTime(15, 0));

        // Arrange
        $terminal = Terminal::factory()->create();

        $session = Session::factory()->for($terminal)->createQuietly([
            'started_at' => now()->setTime(9, 0),
            'expected_minutes' => SessionType::StandardDuration->expectedDuration(),
            'type' => SessionType::StandardDuration,
        ]);

        $timeClock = TimeClock::factory()->for($session)->create([
            'started_at' => now()->setTime(9, 0),
        ]);

        $timeClock->update([
            'ended_at' => now(),
        ]);

        $sessionTimeRemainingService = new SessionTimeRemainingService($session);

        // Act
        $remainingMinutes = $sessionTimeRemainingService->getPreCalculatedRemainingMinutes();

        // Assert
        $this->assertEquals(60, $remainingMinutes);
    }

    public function test_it_returns_remaining_live_minutes_with_time_clock_in_progress()
    {
        Carbon::setTestNow(now()->setTime(15, 0));

        // Arrange
        $terminal = Terminal::factory()->create();

        $session = Session::factory()->for($terminal)->createQuietly([
            'started_at' => now()->setTime(9, 0),
            'expected_minutes' => SessionType::StandardDuration->expectedDuration(),
            'type' => SessionType::StandardDuration,
        ]);

        TimeClock::factory()->for($session)->create([
            'started_at' => now()->setTime(9, 0),
        ]);

        $sessionTimeRemainingService = new SessionTimeRemainingService($session);

        // Act
        $remainingMinutes = $sessionTimeRemainingService->getPreCalculatedRemainingMinutes();

        // Assert
        $this->assertEquals(60, $remainingMinutes);
    }

    public function test_it_does_not_include_non_trackable_time_clock_when_returning_live_minutes()
    {
        Carbon::setTestNow(now()->setTime(15, 0));

        // Arrange
        $terminal = Terminal::factory()->create();

        $session = Session::factory()->for($terminal)->createQuietly([
            'started_at' => now()->setTime(9, 0),
            'expected_minutes' => SessionType::StandardDuration->expectedDuration(),
            'type' => SessionType::StandardDuration,
        ]);

        $timeClock = TimeClock::factory()->for($session)->create([
            'started_at' => now()->setTime(9, 0),
        ]);

        $timeClock->update([
            'ended_at' => now()->setTime(14, 0),
        ]);

        TimeClock::factory()->for($session)->create([
            'started_at' => now()->setTime(14, 0),
            'type' => TimeClockType::OffDuty,
        ]);

        $sessionTimeRemainingService = new SessionTimeRemainingService($session);

        // Act
        $remainingMinutes = $sessionTimeRemainingService->getPreCalculatedRemainingMinutes();

        // Assert
        $this->assertEquals(120, $remainingMinutes);
    }
}
