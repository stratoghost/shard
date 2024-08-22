<?php

namespace Tests\Feature\Livewire;

use App\Models\Session;
use App\Models\User;
use App\TerminalStateType;
use Livewire\Volt\Volt;
use Tests\TestCase;

class StateChangerTest extends TestCase
{
    public function test_it_gets_the_current_terminal()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();
        $session = Session::factory()->for($terminal)->create();

        $this->actingAs($user);

        // Act
        $terminal = $terminal->fresh();

        // Assert
        Volt::test('partials.state-changer')
            ->assertSet('terminal', $terminal)
            ->assertHasNoErrors();
    }

    public function test_it_can_change_the_terminal_state_when_there_is_a_session()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();
        $session = Session::factory()->for($terminal)->create();

        $this->actingAs($user);

        // Act
        $component = Volt::test('partials.state-changer')
            ->set('selectedTerminalState', TerminalStateType::Busy)
            ->call('changeState')
            ->assertHasNoErrors();

        // Assert
        $this->assertDatabaseHas('terminals', [
            'id' => $terminal->id,
            'state' => TerminalStateType::Busy,
        ]);
    }

    public function test_it_reverts_to_saved_terminal_state_when_unable_to_activate_new_state()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $this->actingAs($user);

        // Act
        $component = Volt::test('partials.state-changer')
            ->set('selectedTerminalState', TerminalStateType::Available)
            ->call('changeState')
            ->assertSet('selectedTerminalState', TerminalStateType::Unavailable)
            ->assertHasNoErrors();

        // Assert
        $this->assertDatabaseHas('terminals', [
            'id' => $terminal->id,
            'state' => TerminalStateType::Unavailable,
        ]);
    }

    public function test_it_gets_current_terminal_state_as_trackable_state_bool()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();
        $session = Session::factory()->for($terminal)->create();

        $terminal->updateQuietly([
            'state' => TerminalStateType::Busy,
        ]);

        $this->actingAs($user);

        // Act
        $terminal = $terminal->fresh();

        // Assert
        Volt::test('partials.state-changer')
            ->assertSet('trackableState', true)
            ->assertHasNoErrors();
    }

    public function test_it_initialises_activated_terminal_state()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $terminal->updateQuietly([
            'state' => TerminalStateType::Busy,
        ]);

        $this->actingAs($user);

        // Act
        $terminal = $terminal->fresh();

        // Assert
        Volt::test('partials.state-changer')
            ->assertSet('selectedTerminalState', TerminalStateType::Busy)
            ->assertHasNoErrors();
    }
}
