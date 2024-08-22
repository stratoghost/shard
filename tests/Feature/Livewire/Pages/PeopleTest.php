<?php

namespace Tests\Feature\Livewire\Pages;

use App\Models\Person;
use App\Models\Session;
use App\Models\User;
use App\Services\SessionInstanceService;
use App\SessionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PeopleTest extends TestCase
{
    use RefreshDatabase;

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
        Volt::test('pages.people')
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
        $terminal = $terminal->fresh();

        // Assert
        Volt::test('pages.people')
            ->assertSet('terminal', $terminal)
            ->assertHasNoErrors();
    }

    public function test_it_can_add_a_person()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        Session::factory()->create([
            'terminal_id' => $terminal->id,
        ]);

        $session = $terminal->sessions()->first();

        $this->actingAs($user);

        // Act
        $response = Volt::test('pages.people')
            ->assertSet('terminal', $terminal)
            ->assertSet('session', $session)
            ->set('createPerson.first_name', 'John')
            ->set('createPerson.last_name', 'Doe')
            ->set('createPerson.email', 'john.doe@example.com')
            ->set('createPerson.contact_number', '07777777777')
            ->set('createPerson.type', 'manager')
            ->call('addPerson');

        // Assert
        $response->assertHasNoErrors();

        $this->assertDatabaseHas('people', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'contact_number' => '07777777777',
            'type' => 'manager',
            'terminal_id' => $terminal->id,
        ]);
    }

    public function test_it_validates_add_person_form(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        Session::factory()->create([
            'terminal_id' => $terminal->id,
        ]);

        $session = $terminal->sessions()->first();

        $this->actingAs($user);

        // Act
        $response = Volt::test('pages.people')
            ->assertSet('terminal', $terminal)
            ->assertSet('session', $session)
            ->set('createPerson.first_name', '')
            ->set('createPerson.last_name', '')
            ->set('createPerson.email', '')
            ->set('createPerson.contact_number', '')
            ->set('createPerson.type', '')
            ->call('addPerson');

        // Assert
        $response->assertHasErrors([
            'createPerson.first_name' => 'required',
            'createPerson.last_name' => 'required',
            'createPerson.type' => 'required',
        ]);
    }

    public function test_it_resets_the_add_person_form_after_adding_a_person(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        Session::factory()->create([
            'terminal_id' => $terminal->id,
        ]);

        $session = $terminal->sessions()->first();

        $this->actingAs($user);

        // Act
        $response = Volt::test('pages.people')
            ->set('createPerson.first_name', 'John')
            ->set('createPerson.last_name', 'Doe')
            ->set('createPerson.email', 'john.doe@example.com')
            ->set('createPerson.contact_number', '07777777777')
            ->set('createPerson.type', 'manager')
            ->call('addPerson');

        // Assert
        $response->assertSet('createPerson.first_name', '')
            ->assertSet('createPerson.last_name', '')
            ->assertSet('createPerson.email', '')
            ->assertSet('createPerson.contact_number', '')
            ->assertSet('createPerson.type', '');
    }

    public function test_it_returns_people(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        Person::factory()->count(5)->for($terminal)->create();

        $this->actingAs($user);

        // Act
        $people = $terminal->people;

        // Assert
        $component = Volt::test('pages.people')
            ->assertHasNoErrors();

        foreach ($people as $person) {
            $component->assertSee($person->first_name);
        }
    }

    public function test_it_searches_for_people(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        Person::factory()->create(['terminal_id' => $terminal->id, 'first_name' => 'Lucy']);
        Person::factory()->create(['terminal_id' => $terminal->id, 'first_name' => 'John']);
        Person::factory()->create(['terminal_id' => $terminal->id, 'first_name' => 'Test', 'last_name' => 'John']);
        Person::factory()->create(['terminal_id' => $terminal->id, 'first_name' => 'Test', 'email' => 'John']);

        Person::factory()->times(3)->create([
            'terminal_id' => $terminal->id,
            'first_name' => 'TEST',
            'last_name' => 'TEST',
            'email' => 'TEST',
            'contact_number' => 'TEST',
        ]);

        $this->actingAs($user);

        // Act
        $response = Volt::test('pages.people')
            ->set('search', 'John');

        // Assert
        $response->assertHasNoErrors()
            ->assertCount('people', 3);
    }

    public function test_it_can_delete_a_person(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $person = Person::factory()->for($terminal)->create();

        $this->actingAs($user);

        // Act
        $response = Volt::test('pages.people')
            ->set('person', $person)
            ->call('deletePerson');

        // Assert
        $response->assertHasNoErrors();

        $this->assertSoftDeleted('people', [
            'id' => $person->id,
        ]);
    }

    public function test_it_can_edit_a_person(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $person = Person::factory()->for($terminal)->create();

        $editPerson = [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'type' => 'manager',
        ];

        $this->actingAs($user);

        // Act
        $response = Volt::test('pages.people')
            ->set('person', $person)
            ->set('editPerson', $editPerson)
            ->call('updatePerson', $person->id)
            ->assertHasNoErrors();

        // Assert
        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'first_name' => 'John',
            'last_name' => 'Smith',
            'type' => 'manager',
            'terminal_id' => $terminal->id,
        ]);
    }

    public function test_it_can_edit_an_archived_person(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $person = Person::factory()->for($terminal)->create([
            'deleted_at' => now(),
        ]);

        $editPerson = [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'type' => 'manager',
        ];

        $this->actingAs($user);

        // Act
        $response = Volt::test('pages.people')
            ->set('person', $person)
            ->set('editPerson', $editPerson)
            ->call('updatePerson', $person->id)
            ->assertHasNoErrors();

        // Assert
        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'first_name' => 'John',
            'last_name' => 'Smith',
            'type' => 'manager',
            'terminal_id' => $terminal->id,
        ]);
    }

    public function test_it_can_restore_an_archived_person(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $person = Person::factory()->for($terminal)->create([
            'deleted_at' => now(),
        ]);

        $this->actingAs($user);

        // Act
        $response = Volt::test('pages.people')
            ->set('person', $person)
            ->call('restorePerson')
            ->assertHasNoErrors();

        // Assert
        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'deleted_at' => null,
        ]);
    }
}
