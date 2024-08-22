<?php

namespace Tests\Unit\Listeners\TimeClocks;

use App\Events\Sessions\SessionEndedEvent;
use App\Listeners\TimeClocks\StopTimeClockListener;
use App\Models\TimeClock;
use Tests\TestCase;

class StopTimeClockListenerTest extends TestCase
{
    public function test_it_stops_running_time_clocks()
    {
        // Arrange
        $timeClock = TimeClock::factory()->forSession([
            'minutes_on' => 30,
            'expected_minutes' => 30,
        ])->create([
            'started_at' => now()->subMinutes(30),
            'ended_at' => null,
        ]);

        $sessionEndedEvent = new SessionEndedEvent($timeClock->session);
        $stopTimeClocksListener = new StopTimeClockListener;

        // Act
        $stopTimeClocksListener->handle($sessionEndedEvent);

        // Assert
        $this->assertDatabaseMissing('time_clocks', [
            'id' => $timeClock->id,
            'session_id' => $timeClock->session_id,
            'ended_at' => null,
        ]);
    }

    public function test_it_calculates_duration_when_stopping_running_time_clock()
    {
        // Arrange
        $timeClock = TimeClock::factory()->forSession([
            'minutes_on' => 30,
            'expected_minutes' => 30,
        ])->create([
            'started_at' => now()->subMinutes(30),
            'ended_at' => null,
        ]);

        $sessionEndedEvent = new SessionEndedEvent($timeClock->session);
        $stopTimeClocksListener = new StopTimeClockListener;

        // Act
        $stopTimeClocksListener->handle($sessionEndedEvent);

        // Assert
        $this->assertDatabaseHas('time_clocks', [
            'id' => $timeClock->id,
            'duration' => 30,
            'session_id' => $timeClock->session_id,
        ]);
    }
}
