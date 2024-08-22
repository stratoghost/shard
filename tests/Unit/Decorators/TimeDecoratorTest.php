<?php

namespace Tests\Unit\Decorators;

use App\Decorators\TimeDecorator;
use App\Models\Session;
use App\Models\Terminal;
use App\Models\TimeClock;
use App\Services\SessionTimeRemainingService;
use App\SessionType;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TimeDecoratorTest extends TestCase
{
    public function test_it_formats_remaining_expected_minutes_to_formatted_time()
    {
        // Arrange
        $expectedTimeOn = 164;
        $actualTimeOn = 20;

        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->createQuietly([
            'started_at' => now()->subMinutes(162),
            'expected_minutes' => $expectedTimeOn,
            'type' => SessionType::StandardDuration,
        ]);

        $timeClock = TimeClock::factory()->for($session)->create([
            'started_at' => now()->subMinutes($actualTimeOn),
        ]);

        $timeClock->update([
            'ended_at' => now(),
        ]);

        // Act
        $formattedTime = resolve(TimeDecorator::class)->remainingMinutesFromExpectedMinutes($expectedTimeOn, $actualTimeOn);

        // Assert
        $this->assertEquals('02:24', $formattedTime);
    }

    public function test_it_formats_remaining_minutes_from_integer()
    {
        Carbon::setTestNow(now()->setTime(10, 0));

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

        $remainingMinutes = resolve(SessionTimeRemainingService::class, [
            'session' => $session,
        ])->getPreCalculatedRemainingMinutes();

        // Act
        $formattedTime = resolve(TimeDecorator::class)->remainingMinutesToFormattedTime($remainingMinutes);

        // Assert
        $this->assertEquals('06:00', $formattedTime);
    }

    public function test_it_formats_expected_session_end_time_to_formatted_time()
    {
        Carbon::setTestNow(now()->setTime(12, 0));

        // Arrange
        $expectedTimeOn = 164;
        $actualTimeOn = 20;

        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->createQuietly([
            'started_at' => now()->subMinutes(162),
            'expected_minutes' => $expectedTimeOn,
            'type' => SessionType::StandardDuration,
        ]);

        $timeClock = TimeClock::factory()->for($session)->create([
            'started_at' => now()->subMinutes($actualTimeOn),
        ]);

        $timeClock->update([
            'ended_at' => now(),
        ]);

        // Act
        $formattedTime = resolve(TimeDecorator::class)->expectedEndTimeFromRemainingMinutes($expectedTimeOn, $actualTimeOn);

        // Assert
        $this->assertEquals('14:24', $formattedTime);
    }

    public function test_it_formats_minutes_worked_over_expected_minutes_as_formatted_time()
    {
        Carbon::setTestNow(now()->setTime(13, 30));

        // Arrange
        $expectedTimeOn = 60;
        $actualTimeOn = 90;

        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->createQuietly([
            'started_at' => now()->setTime(12, 0),
            'expected_minutes' => $expectedTimeOn,
            'type' => SessionType::StandardDuration,
        ]);

        $timeClock = TimeClock::factory()->for($session)->create([
            'started_at' => now()->setTime(12, 0),
        ]);

        $timeClock->update([
            'ended_at' => now(),
        ]);

        // Act
        $formattedTime = resolve(TimeDecorator::class)->remainingMinutesFromExpectedMinutes($expectedTimeOn, $actualTimeOn);

        // Assert
        $this->assertEquals('-00:30', $formattedTime);
    }

    public function test_it_formats_session_started_as_formatted_time()
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();

        // Act
        $formattedTime = resolve(TimeDecorator::class)->asHoursAndMinutes($session->started_at);

        // Assert
        $this->assertEquals($session->started_at->format('H:i'), $formattedTime);
    }

    public function test_it_formats_original_session_end_time_as_formatted_time()
    {
        Carbon::setTestNow(now()->setTime(13, 30));

        // Arrange
        $expectedMinutes = 60;

        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->createQuietly([
            'started_at' => now()->setTime(12, 0),
            'expected_minutes' => $expectedMinutes,
            'type' => SessionType::StandardDuration,
        ]);

        $timeClock = TimeClock::factory()->for($session)->create([
            'started_at' => now()->setTime(12, 0),
        ]);

        $timeClock->update([
            'ended_at' => now(),
        ]);

        // Act
        $formattedTime = resolve(TimeDecorator::class)->expectedEndTimeFromExpectedMinutes($session->started_at, $expectedMinutes);

        // Assert
        $this->assertEquals('13:00', $formattedTime);
    }
}
