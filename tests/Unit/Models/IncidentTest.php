<?php

namespace Tests\Unit\Models;

use App\IncidentGradeType;
use App\IncidentType;
use App\Models\Attachment;
use App\Models\Incident;
use App\Models\Session;
use App\Models\Terminal;
use App\Models\Trace;
use App\TraceLinkType;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class IncidentTest extends TestCase
{
    public function test_it_creates_a_model(): void
    {
        // Arrange
        $incident = Incident::factory()->make();

        // Assert
        $incident->save();

        // Assert
        $this->assertDatabaseHas('incidents', ['id' => $incident->id]);
    }

    public function test_it_returns_terminal(): void
    {
        // Arrange
        $incident = Incident::factory()->create();

        // Act
        $terminal = $incident->terminal;

        // Assert
        $this->assertNotNull($terminal);
        $this->assertEquals($incident->terminal_id, $terminal->id);
    }

    public function test_it_casts_type_to_enum(): void
    {
        // Arrange
        $incident = Incident::factory()->make();

        // Act
        $incident->save();

        // Assert
        $this->assertInstanceOf(IncidentType::class, $incident->type);
    }

    public function test_it_casts_started_at_to_carbon(): void
    {
        // Arrange
        $incident = Incident::factory()->make();

        // Act
        $incident->save();

        // Assert
        $this->assertInstanceOf(Carbon::class, $incident->started_at);
    }

    public function test_it_casts_resolved_at_to_carbon(): void
    {
        // Arrange
        $incident = Incident::factory()->make(['resolved_at' => now()]);

        // Act
        $incident->save();

        // Assert
        $this->assertInstanceOf(Carbon::class, $incident->resolved_at);
    }

    public function test_it_casts_ended_at_to_carbon(): void
    {
        // Arrange
        $incident = Incident::factory()->make(['ended_at' => now()]);

        // Act
        $incident->save();

        // Assert
        $this->assertInstanceOf(Carbon::class, $incident->ended_at);
    }

    public function test_it_casts_grade_to_enum(): void
    {
        // Arrange
        $incident = Incident::factory()->make();

        // Act
        $incident->save();

        // Assert
        $this->assertInstanceOf(IncidentGradeType::class, $incident->grade);
    }

    public function test_it_returns_traces(): void
    {
        // Arrange
        $incident = Incident::factory()->create();

        $trace = Trace::factory()->for($incident->terminal)->create([
            'traceable_type' => Incident::class,
            'traceable_id' => $incident->id,
        ]);

        // Act
        $traces = $incident->traces;

        // Assert
        $this->assertCount(1, $traces);
        $this->assertTrue($traces->contains($trace));
        $this->assertEquals($trace->traceable_id, $incident->id);
        $this->assertEquals($incident->terminal_id, $trace->terminal_id);
    }

    public function test_it_returns_linkable_traces(): void
    {
        // Arrange
        $incident = Incident::factory()->create();
        $trace = Trace::factory()->for($incident->terminal)->create([
            'traceable_type' => Incident::class,
            'traceable_id' => $incident->id,
        ]);

        $incident->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $incident->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertTrue($linkableTraces->contains($trace));
        $this->assertEquals($incident->terminal_id, $linkableTraces->first()->terminal_id);
    }

    public function test_it_returns_linkable_traces_with_link_type(): void
    {
        // Arrange
        $incident = Incident::factory()->create();
        $trace = Trace::factory()->for($incident->terminal)->create([
            'traceable_type' => Incident::class,
            'traceable_id' => $incident->id,
        ]);

        $incident->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $incident->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertEquals(TraceLinkType::Related->value, $linkableTraces->first()->pivot->trace_link_type);
        $this->assertTrue($linkableTraces->contains($trace));
        $this->assertEquals($incident->terminal_id, $linkableTraces->first()->terminal_id);
    }

    public function test_it_returns_linked_attachments(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();

        $incident = Incident::factory()->for($terminal)->create();

        $attachment = Attachment::factory()->create([
            'session_id' => $session->id,
            'terminal_id' => $terminal->id,
            'attachable_id' => $incident->id,
            'attachable_type' => Incident::class,
        ]);

        // Act
        $attachments = $incident->attachments;

        // Assert
        $this->assertCount(1, $attachments);
        $this->assertTrue($attachments->contains($attachment));
    }

    public function test_it_returns_open_incidents_through_scope(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        Incident::factory()->for($terminal)->create(['grade' => IncidentGradeType::Information]);

        // Act
        $incidents = Incident::unresolved()
            ->where('terminal_id', $terminal->id)
            ->get();

        // Assert
        $this->assertCount(1, $incidents);
    }
}
