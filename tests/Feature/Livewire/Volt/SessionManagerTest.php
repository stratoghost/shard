<?php

namespace Tests\Feature;

use App\Models\Session;
use App\Models\TimeClock;
use App\Models\User;
use App\Services\SessionInstanceService;
use App\SessionType;
use App\TerminalStateType;
use Livewire\Volt\Volt;
use Tests\TestCase;

class SessionManagerTest extends TestCase
{
    public function test_it_can_start_a_session_with_normal_duration(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $this->actingAs($user);

        // Act
        $component = Volt::test('partials.session-manager')
            ->set('sessionType', SessionType::StandardDuration)
            ->call('startSession');

        $component
            ->assertHasNoErrors()
            ->assertRedirect();

        // Assert
        $this->assertDatabaseHas('sessions', [
            'terminal_id' => $terminal->id,
            'type' => SessionType::StandardDuration,
            'expected_minutes' => SessionType::StandardDuration->expectedDuration(),
        ]);

        $this->assertDatabaseHas('terminals', [
            'user_id' => $user->id,
            'state' => TerminalStateType::Available,
        ]);
    }

    public function test_it_can_start_a_session_with_custom_duration(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $this->actingAs($user);

        // Act
        $component = Volt::test('partials.session-manager')
            ->set('sessionType', SessionType::CustomDuration)
            ->set('expectedMinutes', 30)
            ->call('startSession');

        $component
            ->assertHasNoErrors()
            ->assertRedirect();

        // Assert
        $this->assertDatabaseHas('sessions', [
            'terminal_id' => $terminal->id,
            'type' => SessionType::CustomDuration,
            'expected_minutes' => 30,
        ]);

        $this->assertDatabaseHas('terminals', [
            'user_id' => $user->id,
            'state' => TerminalStateType::Available,
        ]);
    }

    public function test_it_can_start_a_session_in_overtime()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $this->actingAs($user);

        // Act
        $component = Volt::test('partials.session-manager')
            ->set('sessionType', SessionType::OnCall)
            ->set('expectedMinutes', 0)
            ->call('startSession');

        $component
            ->assertHasNoErrors()
            ->assertRedirect();

        // Assert
        $this->assertDatabaseHas('sessions', [
            'terminal_id' => $terminal->id,
            'type' => SessionType::OnCall,
            'expected_minutes' => 0,
        ]);

        $this->assertDatabaseHas('terminals', [
            'user_id' => $user->id,
            'state' => TerminalStateType::Available,
        ]);
    }

    public function test_it_can_start_a_session_in_overtime_with_grace_period()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $this->actingAs($user);

        // Act
        $component = Volt::test('partials.session-manager')
            ->set('sessionType', SessionType::OnCall)
            ->set('expectedMinutes', 5)
            ->call('startSession');

        $component
            ->assertHasNoErrors()
            ->assertRedirect();

        // Assert
        $this->assertDatabaseHas('sessions', [
            'terminal_id' => $terminal->id,
            'type' => SessionType::OnCall,
            'expected_minutes' => 5,
        ]);

        $this->assertDatabaseHas('terminals', [
            'user_id' => $user->id,
            'state' => TerminalStateType::Available,
        ]);
    }

    public function test_it_gets_the_active_session()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $this->actingAs($user);

        // Act
        resolve(SessionInstanceService::class, [
            'terminal' => $terminal,
        ])->startSession(SessionType::StandardDuration);

        $session = $terminal->sessions()->first();

        // Assert
        Volt::test('partials.session-manager')
            ->assertSet('session', $session)
            ->assertHasNoErrors();
    }

    public function test_it_gets_the_current_terminal()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $this->actingAs($user);

        // Act
        resolve(SessionInstanceService::class, [
            'terminal' => $terminal,
        ])->startSession(SessionType::StandardDuration);

        $terminal = $terminal->fresh();

        // Assert
        Volt::test('partials.session-manager')
            ->assertSet('terminal', $terminal)
            ->assertHasNoErrors();
    }

    public function test_it_returns_the_remaining_post_calculated_session_time()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $this->actingAs($user);

        // Act & Assert
        resolve(SessionInstanceService::class, [
            'terminal' => $terminal,
        ])->startSession(SessionType::StandardDuration);

        $component = Volt::test('partials.session-manager')
            ->assertSet('remainingMinutesInSession', SessionType::StandardDuration->expectedDuration())
            ->assertHasNoErrors();
    }

    public function test_it_returns_the_remaining_pre_calculated_session_time()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $session = Session::factory()->for($terminal)->createQuietly([
            'started_at' => now()->subMinutes(30),
            'expected_minutes' => 60,
            'type' => SessionType::CustomDuration,
        ]);

        TimeClock::factory()->for($session)->create([
            'started_at' => now()->subMinutes(30),
        ]);

        $this->actingAs($user);

        // Act & Assert
        Volt::test('partials.session-manager')
            ->assertSet('remainingMinutesInSession', 30)
            ->assertHasNoErrors();
    }

    public function test_it_can_end_a_session()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();
        $session = Session::factory()->for($terminal)->create();

        $this->actingAs($user);

        $sessionInstanceService = resolve(SessionInstanceService::class, [
            'terminal' => $terminal,
        ]);

        // Act
        Volt::test('partials.session-manager')
            ->call('endSession')
            ->assertRedirectToRoute('home')
            ->assertHasNoErrors();

        // Assert
        $session->refresh();

        $this->assertNotNull($session->ended_at);

        $this->assertDatabaseMissing('time_clocks', [
            'session_id' => $session->id,
            'ended_at' => null,
        ]);
    }

    public function test_it_sets_default_expected_minutes_when_session_type_changed(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $this->actingAs($user);

        // Act
        Volt::test('partials.session-manager')
            ->assertSet('expectedMinutes', SessionType::StandardDuration->expectedDuration())
            ->set('sessionType', SessionType::CustomDuration)
            ->call('updateExpectedMinutes')
            ->assertSet('expectedMinutes', SessionType::CustomDuration->expectedDuration());
    }
}
