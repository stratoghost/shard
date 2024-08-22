<?php

namespace Tests\Feature\Livewire\Pages;

use App\Models\Collection;
use App\Models\Session;
use App\Models\User;
use App\Services\SessionInstanceService;
use App\SessionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class CollectionsTest extends TestCase
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
        Volt::test('pages.collections')
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
        Volt::test('pages.collections')
            ->assertSet('terminal', $terminal)
            ->assertHasNoErrors();
    }

    public function test_it_can_add_a_collection()
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
        $component = Volt::test('pages.collections')
            ->assertSet('terminal', $terminal)
            ->assertSet('session', $session)
            ->set('createCollection.name', 'Test Collection')
            ->set('createCollection.description', 'Test Description')
            ->call('addCollection');

        // Assert
        $component
            ->assertHasNoErrors();

        $this->assertDatabaseHas('collections', [
            'name' => 'Test Collection',
            'description' => 'Test Description',
            'terminal_id' => $terminal->id,
        ]);
    }

    public function test_it_validates_add_collection_form(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $this->actingAs($user);

        // Act
        $component = Volt::test('pages.collections')
            ->assertSet('terminal', $terminal)
            ->set('createCollection.name', '')
            ->set('createCollection.description', '')
            ->call('addCollection');

        // Assert
        $component
            ->assertHasErrors(['createCollection.name' => 'required']);
    }

    public function test_it_resets_add_collection_form_after_adding_collection(): void
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
        $component = Volt::test('pages.collections')
            ->assertSet('terminal', $terminal)
            ->assertSet('session', $session)
            ->set('createCollection.name', 'Test Collection')
            ->set('createCollection.description', 'Test Description')
            ->call('addCollection');

        // Assert
        $component
            ->assertHasNoErrors()
            ->assertSet('createCollection.name', '')
            ->assertSet('createCollection.description', '');
    }

    public function test_it_returns_collections(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $collections = Collection::factory()->count(3)->create([
            'terminal_id' => $terminal->id,
        ]);

        $this->actingAs($user);

        // Act
        $component = Volt::test('pages.collections')
            ->assertHasNoErrors();

        // Assert
        foreach ($collections as $collection) {
            $component->assertSee($collection->name);
        }
    }

    public function test_it_can_delete_a_collection(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $collection = Collection::factory()->create([
            'terminal_id' => $terminal->id,
        ]);

        $this->actingAs($user);

        // Act
        $component = Volt::test('pages.collections')
            ->set('collection', $collection)
            ->call('deleteCollection', $collection->id);

        // Assert
        $component->assertHasNoErrors();

        $this->assertSoftDeleted('collections', [
            'id' => $collection->id,
        ]);
    }

    public function test_it_can_edit_a_collection(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $collection = Collection::factory()->create([
            'terminal_id' => $terminal->id,
        ]);

        $this->actingAs($user);

        // Act
        $component = Volt::test('pages.collections')
            ->set('collection', $collection)
            ->set('editCollection.name', 'Updated Name')
            ->set('editCollection.description', 'Updated Description')
            ->call('updateCollection', $collection->id);

        // Assert
        $component->assertHasNoErrors();

        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ]);
    }

    public function test_it_validates_edit_collection_form(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $collection = Collection::factory()->create([
            'terminal_id' => $terminal->id,
        ]);

        $this->actingAs($user);

        // Act
        $component = Volt::test('pages.collections')
            ->set('collection', $collection)
            ->set('editCollection.name', '')
            ->set('editCollection.description', '')
            ->call('updateCollection', $collection->id);

        // Assert
        $component
            ->assertHasErrors(['editCollection.name' => 'required']);
    }

    public function test_it_resets_edit_collection_form_after_editing_collection(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $collection = Collection::factory()->create([
            'terminal_id' => $terminal->id,
        ]);

        $this->actingAs($user);

        // Act
        $component = Volt::test('pages.collections')
            ->set('collection', $collection)
            ->set('editCollection.name', 'Updated Name')
            ->set('editCollection.description', 'Updated Description')
            ->call('updateCollection', $collection->id);

        // Assert
        $component
            ->assertHasNoErrors()
            ->assertSet('editCollection.name', '')
            ->assertSet('editCollection.description', '');
    }

    public function test_it_can_restore_a_collection(): void
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $collection = Collection::factory()->create([
            'terminal_id' => $terminal->id,
            'deleted_at' => now(),
        ]);

        $this->actingAs($user);

        // Act
        $component = Volt::test('pages.collections')
            ->set('collection', $collection)
            ->call('restoreCollection', $collection->id);

        // Assert
        $component->assertHasNoErrors();

        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'deleted_at' => null,
        ]);
    }
}
