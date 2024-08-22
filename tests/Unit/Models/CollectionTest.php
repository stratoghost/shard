<?php

namespace Tests\Unit\Models;

use App\Models\Collection;
use App\Models\Trace;
use App\TraceLinkType;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    public function test_it_can_create_a_model(): void
    {
        // Arrange
        $collection = Collection::factory()->make();

        // Act
        $collection->save();

        // Assert
        $this->assertTrue($collection->exists);

        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'name' => $collection->name,
            'description' => $collection->description,
            'terminal_id' => $collection->terminal_id,
        ]);
    }

    public function test_it_returns_a_terminal(): void
    {
        // Arrange
        $collection = Collection::factory()->create();

        // Act
        $terminal = $collection->terminal;

        // Assert
        $this->assertEquals($collection->terminal_id, $terminal->id);
    }

    public function test_it_returns_traces(): void
    {
        // Arrange
        $collection = Collection::factory()->create();
        $trace = Trace::factory()->for($collection->terminal)->create([
            'traceable_id' => $collection->id,
            'traceable_type' => Collection::class,
        ]);

        // Act
        $traces = $collection->traces;

        // Assert
        $this->assertCount(1, $traces);

        $this->assertTrue($traces->contains($trace));

        $this->assertEquals($trace->traceable_id, $collection->id);
        $this->assertEquals($trace->terminal_id, $collection->terminal_id);
    }

    public function test_it_returns_linkable_traces(): void
    {
        // Arrange
        $collection = Collection::factory()->create();
        $trace = Trace::factory()->for($collection->terminal)->create([
            'traceable_id' => $collection->id,
            'traceable_type' => Collection::class,
        ]);

        $collection->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $collection->links;

        // Assert
        $this->assertCount(1, $linkableTraces);

        $this->assertTrue($linkableTraces->contains($trace));

        $this->assertEquals($trace->traceable_id, $collection->id);
        $this->assertEquals($trace->terminal_id, $collection->terminal_id);
    }

    public function test_it_returns_linkable_traces_with_link_type(): void
    {
        // Arrange
        $collection = Collection::factory()->create();
        $trace = Trace::factory()->for($collection->terminal)->create([
            'traceable_id' => $collection->id,
            'traceable_type' => Collection::class,
        ]);

        $collection->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $collection->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertEquals(TraceLinkType::Related->value, $linkableTraces->first()->pivot->trace_link_type);

        $this->assertTrue($linkableTraces->contains($trace));

        $this->assertEquals($trace->traceable_id, $collection->id);
        $this->assertEquals($trace->terminal_id, $collection->terminal_id);
    }
}
