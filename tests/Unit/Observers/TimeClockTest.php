<?php

namespace Tests\Unit\Observers;

use App\Events\TimeClocks\TimeClockEndedEvent;
use App\Models\TimeClock;
use App\TimeClockType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TimeClockTest extends TestCase
{
    use RefreshDatabase;

    public static function dutyMinutesUpdatedWhenDurationCalculatedProvider(): array
    {
        return [
            'test it updates session duty minutes when duration calculated' => [
                'field' => 'minutes_on',
                'started_at' => now()->setTime(8, 0),
                'ended_at' => now()->setTime(8, 30),
                'type' => TimeClockType::OnDuty,
                'expected' => 30,
            ],
            'test it updates session minutes on break when duration calculated' => [
                'field' => 'minutes_off',
                'started_at' => now()->setTime(8, 0),
                'ended_at' => now()->setTime(8, 30),
                'type' => TimeClockType::OffDuty,
                'expected' => 30,
            ],
        ];
    }

    public function test_it_automatically_populates_started_at_when_creating(): void
    {
        // Arrange
        $timeClock = TimeClock::factory()->create();

        // Assert
        $this->assertNotNull($timeClock->refresh()->started_at);
    }

    public function test_it_sets_default_type_when_not_provided(): void
    {
        // Arrange
        $timeClock = TimeClock::factory()->make(['type' => null]);

        // Act
        $timeClock->save();

        // Assert
        $this->assertEquals(TimeClockType::default(), $timeClock->type);
    }

    public function test_it_calculates_time_clock_duration_when_updating_end_timestamp(): void
    {
        // Arrange
        $timeClock = TimeClock::factory()->create();
        $timeClock->started_at = now()->subMinutes(30);
        $timeClock->ended_at = now();

        // Act
        $timeClock->save();

        // Assert
        $this->assertEquals(30, $timeClock->duration);
    }

    public function test_it_updates_session_minutes_on_duration_when_of_type_working(): void
    {
        // Arrange
        Carbon::setTestNow(now());
        $timeClock = TimeClock::factory()->create([
            'started_at' => now()->setTime(8, 0),
            'type' => TimeClockType::OnDuty,
        ]);

        // Act
        $timeClock->ended_at = now()->setTime(8, 30);
        $timeClock->save();

        // Assert
        $this->assertEquals(30, $timeClock->duration);
    }

    public function test_it_dispatches_time_clock_ended_event_when_time_clock_ended(): void
    {
        // Arrange
        Event::fake([
            TimeClockEndedEvent::class,
        ]);

        Carbon::setTestNow(now());

        $timeClock = TimeClock::factory()->create([
            'started_at' => now()->setTime(8, 0),
            'type' => TimeClockType::OnDuty,
        ]);

        // Act
        $timeClock->ended_at = now()->setTime(8, 30);
        $timeClock->save();

        // Assert
        Event::assertDispatched(TimeClockEndedEvent::class);
    }

    #[dataProvider('dutyMinutesUpdatedWhenDurationCalculatedProvider')]
    public function test_it_updates_session_duty_minutes_when_duration_calculated($field, $started_at, $ended_at, $type, $expected): void
    {
        // Arrange
        Carbon::setTestNow(now());
        $timeClock = TimeClock::factory()->create([
            'started_at' => $started_at,
            'type' => $type,
        ]);

        // Act
        $timeClock->ended_at = $ended_at;
        $timeClock->save();

        // Assert
        $this->assertEquals($expected, $timeClock->session->{$field});
    }

    #[dataProvider('dutyMinutesUpdatedWhenDurationCalculatedProvider')]
    public function test_it_adds_to_session_duty_minutes_when_duration_calculated($field, $started_at, $ended_at, $type, $expected): void
    {
        // Arrange
        Carbon::setTestNow(now());
        $existingMinuteDuration = 30;
        $timeClock = TimeClock::factory()->forSession([
            'minutes_on' => $existingMinuteDuration,
            'minutes_off' => $existingMinuteDuration,
        ])->create([
            'started_at' => $started_at,
            'type' => $type,
        ]);

        // Act
        $timeClock->ended_at = $ended_at;
        $timeClock->save();

        // Assert
        $this->assertEquals($expected + $existingMinuteDuration, $timeClock->session->{$field});
    }
}
