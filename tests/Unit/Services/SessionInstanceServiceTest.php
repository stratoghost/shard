<?php

namespace Tests\Unit\Services;

use App\Events\Sessions\SessionEndedEvent;
use App\Exceptions\Sessions\MultipleSessionsStartedException;
use App\Exceptions\Sessions\SessionAlreadyStartedException;
use App\Exceptions\Sessions\SessionNotStartedException;
use App\Models\Session;
use App\Models\Terminal;
use App\Services\SessionInstanceService;
use App\SessionType;
use App\TerminalStateType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SessionInstanceServiceTest extends TestCase
{
    use RefreshDatabase;

    public static function providesSessionTypesWhenLoggingOn(): array
    {
        return [
            [SessionType::StandardDuration, SessionType::StandardDuration->expectedDuration()],
            [SessionType::CustomDuration, SessionType::CustomDuration->expectedDuration()],
            [SessionType::OnCall, SessionType::OnCall->expectedDuration()],
        ];
    }

    public function test_it_logs_on_to_a_session(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $service = new SessionInstanceService($terminal);

        // Act
        $session = $service->startSession(SessionType::StandardDuration);

        // Assert
        $this->assertInstanceOf(Session::class, $session);
        $this->assertTrue($session->exists);
        $this->assertDatabaseHas('sessions', ['id' => $session->id]);
    }

    public function test_it_throws_exception_when_logging_on_to_a_session_with_previous_session_not_logged_out(): void
    {
        // Expect
        $this->expectException(SessionAlreadyStartedException::class);

        // Arrange
        $terminal = Terminal::factory()->create();
        Session::factory()->for($terminal)->create();
        $service = new SessionInstanceService($terminal);

        // Act
        $service->startSession(SessionType::StandardDuration);
    }

    public function test_it_sets_expected_session_duration_when_logging_onto_new_session(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $sessionInstanceService = new SessionInstanceService($terminal);

        // Act
        $session = $sessionInstanceService->startSession(SessionType::CustomDuration, 60);

        // Assert
        $this->assertEquals(60, $session->expected_minutes);
    }

    public function test_it_ends_a_logged_on_session(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $sessionInstanceService = new SessionInstanceService($terminal);
        $session = Session::factory()->for($terminal)->create(['type' => SessionType::StandardDuration]);

        // Act
        $sessionInstanceService->endSession();
        $session->refresh();

        // Assert
        $this->assertNotNull($session->ended_at);
        $this->assertFalse($session->timeClocks()->whereNull('ended_at')->exists());
    }

    public function test_it_dispatches_session_ended_event_when_ending_session(): void
    {
        // Fake
        Event::fake([
            SessionEndedEvent::class,
        ]);

        // Arrange
        $terminal = Terminal::factory()->create();
        $sessionInstanceService = new SessionInstanceService($terminal);
        $session = Session::factory()->for($terminal)->create(['type' => SessionType::StandardDuration]);

        // Act
        $sessionInstanceService->endSession();

        // Assert
        Event::assertDispatched(SessionEndedEvent::class);
    }

    public function test_it_throws_exception_when_ending_session_with_no_logged_on_session(): void
    {
        // Expect
        $this->expectException(SessionNotStartedException::class);

        // Arrange
        $terminal = Terminal::factory()->create();
        $sessionInstanceService = new SessionInstanceService($terminal);

        // Act
        $sessionInstanceService->endSession();
    }

    public function test_it_throws_exception_when_ending_session_with_multiple_sessions_started(): void
    {
        // Expect
        $this->expectException(MultipleSessionsStartedException::class);

        // Arrange
        $terminal = Terminal::factory()->create();
        Session::factory()->for($terminal)->count(2)->create([
            'type' => SessionType::StandardDuration,
        ]);

        $sessionInstanceService = new SessionInstanceService($terminal);

        // Act
        $sessionInstanceService->endSession();
    }

    public function test_it_sets_terminal_state_when_starting_session(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $sessionInstanceService = new SessionInstanceService($terminal);

        // Act
        $sessionInstanceService->startSession(SessionType::StandardDuration);

        // Assert
        $this->assertEquals(TerminalStateType::Available, $terminal->state);
    }

    public function test_it_sets_terminal_state_when_ending_session(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $sessionInstanceService = new SessionInstanceService($terminal);
        $session = Session::factory()->for($terminal)->create(['type' => SessionType::StandardDuration]);

        // Act
        $sessionInstanceService->endSession();

        // Assert
        $this->assertEquals(TerminalStateType::Unavailable, $terminal->state);
    }

    public function test_it_handles_setting_terminal_state_when_ending_session_terminal_state_same(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $sessionInstanceService = new SessionInstanceService($terminal);
        $sessionInstanceService->startSession(SessionType::StandardDuration);
        $terminal->update(['state' => TerminalStateType::Unavailable]);

        // Act
        $sessionInstanceService->endSession();

        // Assert
        $this->assertEquals(TerminalStateType::Unavailable, $terminal->state);
    }

    #[dataProvider('providesSessionTypesWhenLoggingOn')]
    public function test_it_sets_session_type_when_logging_on($sessionType, $sessionMinutes): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $sessionInstanceService = new SessionInstanceService($terminal);

        // Act
        $session = $sessionInstanceService->startSession($sessionType);

        // Assert
        $this->assertEquals($sessionType, $session->type);
        $this->assertEquals($sessionMinutes, $session->expected_minutes);
    }
}
