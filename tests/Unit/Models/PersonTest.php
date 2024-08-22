<?php

namespace Tests\Unit\Models;

use App\Models\Attachment;
use App\Models\Person;
use App\Models\Terminal;
use App\Models\Trace;
use App\PersonType;
use App\TraceLinkType;
use Tests\TestCase;

class PersonTest extends TestCase
{
    public function test_it_can_create_a_model(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $person = Person::factory()->for($terminal)->make();

        // Act
        $person->save();

        // Assert
        $this->assertTrue($person->exists);

        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'first_name' => $person->first_name,
            'terminal_id' => $person->terminal_id,
        ]);
    }

    public function test_it_returns_a_terminal(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $person = Person::factory()->for($terminal)->create();

        // Act
        $terminal = $person->terminal;

        // Assert
        $this->assertEquals($person->terminal_id, $terminal->id);
    }

    public function test_it_casts_type_to_person_type_enum(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $person = Person::factory()->for($terminal)->make();

        // Act
        $person->save();

        // Assert
        $this->assertInstanceOf(PersonType::class, $person->type);
    }

    public function test_it_returns_traces(): void
    {
        // Arrange
        $person = Person::factory()->create();
        $trace = Trace::factory()->for($person->terminal)->create([
            'traceable_id' => $person->id,
            'traceable_type' => Person::class,
        ]);

        // Act
        $traces = $person->traces;

        // Assert
        $this->assertCount(1, $traces);
        $this->assertEquals($trace->id, $traces->first()->id);
    }

    public function test_it_returns_linkable_traces(): void
    {
        // Arrange
        $person = Person::factory()->create();
        $trace = Trace::factory()->for($person->terminal)->create([
            'traceable_id' => $person->id,
            'traceable_type' => Person::class,
        ]);

        $person->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $person->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertTrue($linkableTraces->contains($trace));
        $this->assertEquals($person->terminal_id, $linkableTraces->first()->terminal_id);
    }

    public function test_it_returns_linkable_traces_with_link_type(): void
    {
        // Arrange
        $person = Person::factory()->create();
        $trace = Trace::factory()->for($person->terminal)->create([
            'traceable_id' => $person->id,
            'traceable_type' => Person::class,
        ]);

        $person->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $person->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertEquals(TraceLinkType::Related->value, $linkableTraces->first()->pivot->trace_link_type);
        $this->assertTrue($linkableTraces->contains($trace));
        $this->assertEquals($person->terminal_id, $linkableTraces->first()->terminal_id);
    }

    public function test_it_returns_attachments(): void
    {
        // Arrange
        $person = Person::factory()->create();

        $attachment = Attachment::factory()->create([
            'terminal_id' => $person->terminal_id,
            'attachable_id' => $person->id,
            'attachable_type' => Person::class,
        ]);

        // Act
        $attachments = $person->attachments;

        // Assert
        $this->assertCount(1, $attachments);
        $this->assertTrue($attachments->contains($attachment));
        $this->assertEquals($person->terminal_id, $attachments->first()->terminal_id);
    }

    public function test_it_returns_joined_first_and_last_name(): void
    {
        // Arrange
        $person = Person::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        // Act
        $fullName = $person->name;

        // Assert
        $this->assertEquals('John Doe', $fullName);
    }
}
