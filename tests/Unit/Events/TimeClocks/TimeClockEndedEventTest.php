<?php

namespace Tests\Unit\Events\TimeClocks;

use App\Events\TimeClocks\TimeClockEndedEvent;
use App\Models\TimeClock;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TimeClockEndedEventTest extends TestCase
{
    public function test_it_can_be_dispatched(): void
    {
        // Fake
        Event::fake([
            TimeClockEndedEvent::class,
        ]);

        // Arrange
        $timeClock = TimeClock::factory()->create([
            'started_at' => now()->subMinutes(30),
            'ended_at' => now(),
        ]);

        TimeClockEndedEvent::dispatch($timeClock);

        // Assert
        Event::assertDispatched(TimeClockEndedEvent::class, function ($event) use ($timeClock) {
            return $event->timeClock->is($timeClock);
        });
    }
}
