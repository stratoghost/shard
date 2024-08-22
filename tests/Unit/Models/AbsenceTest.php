<?php

namespace Tests\Unit\Models;

use App\AbsenceType;
use App\Models\Absence;
use App\Models\Attachment;
use App\Models\Session;
use App\Models\Terminal;
use App\Models\Trace;
use App\TraceLinkType;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AbsenceTest extends TestCase
{
    public function test_it_can_add_an_absence(): void
    {
        // Arrange
        $absence = Absence::factory()->make([
            'minutes_absent' => 420,
        ]);

        // Act
        $absence->save();

        // Assert
        $this->assertDatabaseHas('absences', [
            'date' => $absence->date->toDateString(),
            'terminal_id' => $absence->terminal_id,
        ]);
    }

    public function test_it_can_add_an_absence_with_absence_type(): void
    {
        // Arrange
        $absence = Absence::factory()->make([
            'minutes_absent' => 420,
            'type' => AbsenceType::JuryDuty,
        ]);

        // Act
        $absence->save();

        // Assert
        $this->assertDatabaseHas('absences', [
            'date' => $absence->date->toDateString(),
            'type' => $absence->type,
            'terminal_id' => $absence->terminal_id,
        ]);
    }

    public function test_it_returns_a_terminal(): void
    {
        // Arrange
        $absence = Absence::factory()->create([
            'minutes_absent' => 420,
        ]);

        // Act
        $terminal = $absence->terminal;

        // Assert
        $this->assertEquals($absence->terminal_id, $terminal->id);
    }

    public function test_it_casts_date_to_carbon_object(): void
    {
        // Arrange
        $absence = Absence::factory()->make([
            'minutes_absent' => 420,
        ]);

        // Act
        $absence->save();

        // Assert
        $this->assertInstanceOf(Carbon::class, $absence->date);
    }

    public function test_it_casts_authorised_at_to_carbon_object(): void
    {
        // Arrange
        $absence = Absence::factory()->make([
            'minutes_absent' => 420,
            'authorised_at' => Carbon::now(),
        ]);

        // Act
        $absence->save();

        // Assert
        $this->assertInstanceOf(Carbon::class, $absence->authorised_at);
    }

    public function test_it_casts_type_to_absence_type_enum(): void
    {
        // Arrange
        $absence = Absence::factory()->make([
            'type' => AbsenceType::JuryDuty,
        ]);

        // Act
        $absence->save();

        // Assert
        $this->assertInstanceOf(AbsenceType::class, $absence->type);
    }

    public function test_it_returns_traces(): void
    {
        // Arrange
        $absence = Absence::factory()->create();

        $trace = Trace::factory()->create([
            'traceable_type' => Absence::class,
            'traceable_id' => $absence->id,
        ]);

        // Act
        $traces = $absence->traces;

        // Assert
        $this->assertCount(1, $traces);
        $this->assertEquals($trace->id, $traces->first()->id);
        $this->assertEquals($trace->terminal_id, $traces->first()->terminal_id);
    }

    public function test_it_returns_linkable_traces(): void
    {
        // Arrange
        $absence = Absence::factory()->create();

        $traces = Trace::factory()->count(3)->create([
            'traceable_type' => Trace::class,
            'traceable_id' => $absence->id,
        ]);

        $absence->links()->attach($traces->last(), [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $absence->links;

        // Assert
        $this->assertTrue($linkableTraces->contains($traces->last()));
        $this->assertEquals($traces->last()->terminal_id, $linkableTraces->first()->terminal_id);
    }

    public function test_it_returns_linkable_traces_with_link_type(): void
    {
        // Arrange
        $absence = Absence::factory()->create();

        $traces = Trace::factory()->count(3)->create([
            'traceable_type' => Trace::class,
            'traceable_id' => $absence->id,
        ]);

        $absence->links()->attach($traces->last(), [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Affected,
        ]);

        // Act
        $linkableTraces = $absence->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertEquals(TraceLinkType::Affected->value, $linkableTraces->first()->pivot->trace_link_type);
        $this->assertTrue($linkableTraces->contains($traces->last()));
        $this->assertEquals($traces->last()->terminal_id, $linkableTraces->first()->terminal_id);
    }

    public function test_it_returns_attachments(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $absence = Absence::factory()->create();

        $attachment = Attachment::factory()->for($terminal)->create([
            'session_id' => $session->id,
            'attachable_id' => $absence->id,
            'attachable_type' => Absence::class,
        ]);

        // Act
        $attachments = $absence->attachments;

        // Assert
        $this->assertCount(1, $attachments);
        $this->assertEquals($attachment->id, $attachments->first()->id);
        $this->assertEquals($attachment->terminal_id, $attachments->first()->terminal_id);
    }
}
