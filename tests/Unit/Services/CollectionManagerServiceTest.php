<?php

namespace Tests\Unit\Services;

use App\Exceptions\Collections\CollectionAlreadyExistsException;
use App\Exceptions\Common\ModelNotTrashedException;
use App\Models\Collection;
use App\Models\Terminal;
use App\Services\CollectionManagerService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

class CollectionManagerServiceTest extends TestCase
{
    public function test_it_creates_a_collection_for_a_terminal(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $collectionManagerService = new CollectionManagerService($terminal);

        // Act
        $collection = $collectionManagerService->createCollection(['name' => 'Test collection']);

        // Assert
        $this->assertTrue($collection->exists);
        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'name' => 'Test collection',
            'terminal_id' => $terminal->id,
        ]);
    }

    public function test_it_cannot_create_a_duplicate_collection(): void
    {
        // Expect
        $this->expectException(CollectionAlreadyExistsException::class);

        // Arrange
        $terminal = Terminal::factory()->create();
        $collectionManagerService = new CollectionManagerService($terminal);
        $collection = Collection::factory()->for($terminal)->create([
            'name' => 'Test collection',
        ]);

        // Act
        $collectionManagerService->createCollection($collection->attributesToArray());
    }

    public function test_it_can_modify_a_collection_for_terminal(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $collection = Collection::factory()->for($terminal)->create([
            'name' => 'Test collection',
        ]);
        $collectionManagerService = new CollectionManagerService($terminal);

        // Act
        $collectionManagerService->modifyCollection($collection, ['name' => 'New collection']);

        // Assert
        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'name' => 'New collection',
        ]);
    }

    public function test_it_cannot_modify_a_collection_to_become_a_duplicate(): void
    {
        // Expect
        $this->expectException(CollectionAlreadyExistsException::class);

        // Arrange
        $terminal = Terminal::factory()->create();
        $collectionManagerService = new CollectionManagerService($terminal);
        $collection1 = Collection::factory()->for($terminal)->create([
            'name' => 'Test collection 1',
        ]);
        $collection2 = Collection::factory()->for($terminal)->create([
            'name' => 'Test collection 2',
        ]);

        // Act
        $collectionManagerService->modifyCollection($collection1, ['name' => 'Test collection 2']);
    }

    public function test_it_can_archive_a_collection(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $collection = Collection::factory()->for($terminal)->create();
        $collectionManagerService = new CollectionManagerService($terminal);

        // Act
        $collectionManagerService->archiveCollection($collection);

        // Assert
        $this->assertSoftDeleted('collections', ['id' => $collection->id]);
    }

    public function test_it_cannot_archive_a_collection_already_archived(): void
    {
        // Expect
        $this->expectException(ModelNotFoundException::class);

        // Arrange
        $terminal = Terminal::factory()->create();
        $collection = Collection::factory()->for($terminal)->create(['deleted_at' => now()]);
        $collectionManagerService = new CollectionManagerService($terminal);

        // Act
        $collectionManagerService->archiveCollection($collection);
    }

    public function test_it_can_restore_a_collection_from_archive(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $collection = Collection::factory()->for($terminal)->create(['deleted_at' => now()]);
        $collectionManagerService = new CollectionManagerService($terminal);

        // Act
        $collectionManagerService->restoreCollection($collection);

        // Assert
        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'deleted_at' => null,
        ]);
    }

    public function test_it_cannot_restore_a_collection_not_archived(): void
    {
        // Expect
        $this->expectException(ModelNotTrashedException::class);

        // Arrange
        $terminal = Terminal::factory()->create();
        $collection = Collection::factory()->for($terminal)->create();
        $collectionManagerService = new CollectionManagerService($terminal);

        // Act
        $collectionManagerService->restoreCollection($collection);
    }

    public function test_it_can_list_all_collections_for_a_terminal(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $collection1 = Collection::factory()->for($terminal)->create(['name' => 'collection 1']);
        $collection2 = Collection::factory()->for($terminal)->create(['name' => 'collection 2']);
        $collectionManagerService = new CollectionManagerService($terminal);

        // Act
        $collections = $collectionManagerService->listCollections();

        // Assert
        $this->assertCount(2, $collections);
        $this->assertTrue($collections->contains($collection1));
        $this->assertTrue($collections->contains($collection2));
    }

    public function test_it_lists_archived_collections_for_a_terminal(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $collection1 = Collection::factory()->for($terminal)->create(['name' => 'collection 1', 'deleted_at' => now()]);
        $collection2 = Collection::factory()->for($terminal)->create(['name' => 'collection 2', 'deleted_at' => now()]);
        $collection3 = Collection::factory()->for($terminal)->create(['name' => 'collection 3']);
        $collectionManagerService = new CollectionManagerService($terminal);

        // Act
        $collections = $collectionManagerService->listArchivedCollections();

        // Assert
        $this->assertCount(2, $collections);
        $this->assertTrue($collections->contains($collection1));
        $this->assertTrue($collections->contains($collection2));
        $this->assertFalse($collections->contains($collection3));
    }

    public function test_it_lists_active_collections_for_a_terminal(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $collection1 = Collection::factory()->for($terminal)->create(['name' => 'collection 1']);
        $collection2 = Collection::factory()->for($terminal)->create(['name' => 'collection 2']);
        $collection3 = Collection::factory()->for($terminal)->create(['name' => 'collection 3', 'deleted_at' => now()]);
        $collectionManagerService = new CollectionManagerService($terminal);

        // Act
        $collections = $collectionManagerService->listCollections();

        // Assert
        $this->assertCount(2, $collections);
        $this->assertTrue($collections->contains($collection1));
        $this->assertTrue($collections->contains($collection2));
        $this->assertFalse($collections->contains($collection3));
    }
}
