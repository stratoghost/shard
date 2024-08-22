<?php

namespace Tests\Unit\Listeners\Timestamps;

use App\Events\TimeClocks\TimeClockEndedEvent;
use App\Listeners\Snapshots\CreateSnapshotListener;
use App\Models\Session;
use App\Models\TimeClock;
use App\SessionType;
use App\TimeClockType;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CreateSnapshotListenerTest extends TestCase
{
    public function test_it_can_create_a_snapshot(): void
    {
        // Arrange
        $timeClock = TimeClock::factory()->forSession([
            'minutes_on' => 30,
            'expected_minutes' => 30,
        ])->create([
            'started_at' => now()->subMinutes(30),
            'ended_at' => now(),
        ]);

        // Act
        $createSnapshotListener = new CreateSnapshotListener;
        $createSnapshotListener->handle(new TimeClockEndedEvent($timeClock));

        // Assert
        $this->assertDatabaseHas('snapshots', [
            'time_clock_id' => $timeClock->id,
            'session_id' => $timeClock->session_id,
            'terminal_id' => $timeClock->session->terminal_id,
            'minutes_given' => 30,
            'minutes_expected' => 30,
            'balance' => 0,
        ]);
    }

    public function test_it_records_balance(): void
    {
        // Arrange
        Carbon::setTestNow(now()->setTime(16, 00));
        $timeClock = TimeClock::factory()->forSession([
            'minutes_on' => 450,
            'expected_minutes' => 420,
        ])->create([
            'started_at' => now()->setTime(8, 0),
            'ended_at' => now()->setTime(15, 30),
            'duration' => 450,
            'type' => TimeClockType::OnDuty,
        ]);

        // Act
        $createSnapshotListener = new CreateSnapshotListener;
        $createSnapshotListener->handle(new TimeClockEndedEvent($timeClock));

        // Assert
        $this->assertDatabaseHas('snapshots', [
            'time_clock_id' => $timeClock->id,
            'session_id' => $timeClock->session_id,
            'terminal_id' => $timeClock->session->terminal_id,
            'minutes_given' => 450,
            'minutes_expected' => 420,
            'balance' => -30,
        ]);
    }

    public function test_it_records_overtime_balance_when_only_overtime_clocks_exist(): void
    {
        Carbon::setTestNow(now());

        $testSequences = [
            [
                'startingHour' => 8,
                'endingHour' => 9,
                'cumulativeWorkingBalance' => -60,
                'cumulativeGivenMinutes' => 60,
                'cumulativeExpectedMinutes' => 0,
            ],
            [
                'startingHour' => 8,
                'endingHour' => 10,
                'cumulativeWorkingBalance' => -180,
                'cumulativeGivenMinutes' => 180,
                'cumulativeExpectedMinutes' => 0,
            ],
        ];

        // Arrange
        $sharedSession = Session::factory()->create([
            'minutes_on' => 0,
            'expected_minutes' => 0,
        ]);

        foreach ($testSequences as $sequence) {
            $timeClock = TimeClock::factory()->for($sharedSession)->create([
                'started_at' => now()->setTime($sequence['startingHour'], 0),
                'type' => TimeClockType::OnDuty,
            ]);

            // Act
            $timeClock->ended_at = now()->setTime($sequence['endingHour'], 0);
            $timeClock->save();

            $createSnapshotListener = new CreateSnapshotListener;
            $createSnapshotListener->handle(new TimeClockEndedEvent($timeClock));

            $sharedSession->refresh();

            // Assert
            $snapshot = $sharedSession->snapshots->last();

            $this->assertEquals($sequence['cumulativeGivenMinutes'], $snapshot->minutes_given);
            $this->assertEquals($sequence['cumulativeExpectedMinutes'], $snapshot->minutes_expected);
            $this->assertEquals($sequence['cumulativeWorkingBalance'], $snapshot->balance);

            $this->assertEquals($sharedSession->id, $snapshot->session_id);
            $this->assertEquals($sharedSession->terminal_id, $snapshot->terminal_id);
        }
    }

    public function test_it_records_balance_as_expected_with_sequence(): void
    {
        Carbon::setTestNow(now());

        $testSequences = [
            [
                'startingHour' => 8,
                'endingHour' => 11,
                'cumulativeWorkingBalance' => 240,
                'cumulativeGivenMinutes' => 180,
                'cumulativeExpectedMinutes' => 420,
            ],
            [
                'startingHour' => 12,
                'endingHour' => 15,
                'cumulativeWorkingBalance' => 60,
                'cumulativeGivenMinutes' => 360,
                'cumulativeExpectedMinutes' => 420,
            ],
            [
                'startingHour' => 15,
                'endingHour' => 16,
                'cumulativeWorkingBalance' => 0,
                'cumulativeGivenMinutes' => 420,
                'cumulativeExpectedMinutes' => 420,
            ],
            [
                'startingHour' => 16,
                'endingHour' => 17,
                'cumulativeWorkingBalance' => -60,
                'cumulativeGivenMinutes' => 480,
                'cumulativeExpectedMinutes' => 420,
            ],
        ];

        // Arrange
        $sharedSession = Session::factory()->create([
            'minutes_on' => 0,
            'expected_minutes' => 420,
        ]);

        foreach ($testSequences as $sequence) {
            $timeClock = TimeClock::factory()->for($sharedSession)->create([
                'started_at' => now()->setTime($sequence['startingHour'], 0),
                'type' => TimeClockType::OnDuty,
            ]);

            // Act
            $timeClock->ended_at = now()->setTime($sequence['endingHour'], 0);
            $timeClock->save();

            $createSnapshotListener = new CreateSnapshotListener;
            $createSnapshotListener->handle(new TimeClockEndedEvent($timeClock));

            $sharedSession->refresh();

            // Assert
            $snapshot = $sharedSession->snapshots->last();

            $this->assertEquals($sequence['cumulativeGivenMinutes'], $snapshot->minutes_given);
            $this->assertEquals($sequence['cumulativeExpectedMinutes'], $snapshot->minutes_expected);
            $this->assertEquals($sequence['cumulativeWorkingBalance'], $snapshot->balance);

            $this->assertEquals($sharedSession->id, $snapshot->session_id);
            $this->assertEquals($sharedSession->terminal_id, $snapshot->terminal_id);
        }
    }

    public function test_it_always_accumulates_minutes_over_session_type_is_emergency(): void
    {
        // Arrange
        Carbon::setTestNow(now()->setTime(8, 0));
        $timeClock = TimeClock::factory()->forSession([
            'minutes_on' => 0,
            'type' => SessionType::OnCall,
        ])->create([
            'started_at' => now()->setTime(8, 0),
            'type' => TimeClockType::OnDuty,
        ]);

        // Act
        $timeClock->ended_at = now()->setTime(9, 0);
        $timeClock->save();

        $createSnapshotListener = new CreateSnapshotListener;
        $createSnapshotListener->handle(new TimeClockEndedEvent($timeClock));

        $latestSnapshot = $timeClock->session->snapshots->last();

        // Assert
        $this->assertEquals(60, $latestSnapshot->minutes_given);
        $this->assertEquals(0, $latestSnapshot->minutes_expected);
        $this->assertEquals(-60, $latestSnapshot->balance);

        $this->assertEquals($timeClock->session->id, $latestSnapshot->session_id);
        $this->assertEquals($timeClock->session->terminal_id, $latestSnapshot->terminal_id);
    }
}
