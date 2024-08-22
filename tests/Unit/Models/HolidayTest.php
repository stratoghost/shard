<?php

namespace Tests\Unit\Models;

use App\Models\Attachment;
use App\Models\Holiday;
use App\Models\Session;
use App\Models\Terminal;
use App\Models\Trace;
use App\TraceLinkType;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class HolidayTest extends TestCase
{
    public function test_it_can_create_a_model(): void
    {
        // Arrange
        $holiday = Holiday::factory()->make([
            'minutes_authorised' => 420,
        ]);

        // Act
        $holiday->save();

        // Assert
        $this->assertTrue($holiday->exists);
        $this->assertDatabaseHas('holidays', [
            'id' => $holiday->id,
            'date' => $holiday->date->toDateString(),
            'terminal_id' => $holiday->terminal_id,
        ]);
    }

    public function test_it_returns_a_terminal(): void
    {
        // Arrange
        $holiday = Holiday::factory()->create([
            'minutes_authorised' => 420,
        ]);

        // Act
        $terminal = $holiday->terminal;

        // Assert
        $this->assertEquals($holiday->terminal_id, $terminal->id);
        $this->assertInstanceOf(Terminal::class, $terminal);
    }

    public function test_it_casts_date_to_carbon_object(): void
    {
        // Arrange
        $holiday = Holiday::factory()->make([
            'minutes_authorised' => 420,
        ]);

        // Act
        $holiday->save();

        // Assert
        $this->assertInstanceOf(Carbon::class, $holiday->date);
    }

    public function test_it_casts_authorised_at_to_carbon_object(): void
    {
        // Arrange
        $holiday = Holiday::factory()->make([
            'minutes_authorised' => 420,
        ]);

        // Act
        $holiday->save();

        // Assert
        $this->assertInstanceOf(Carbon::class, $holiday->authorised_at);
    }

    public function test_it_casts_cancelled_at_to_carbon_object(): void
    {
        // Arrange
        $holiday = Holiday::factory()->make([
            'minutes_authorised' => 420,
        ]);

        // Act
        $holiday->save();

        // Assert
        $this->assertNull($holiday->cancelled_at);
    }

    public function test_it_returns_traces()
    {
        // Arrange
        $holiday = Holiday::factory()->create();
        $trace = Trace::factory()->for($holiday->terminal)->create([
            'traceable_type' => Holiday::class,
            'traceable_id' => $holiday->id,
        ]);

        // Act
        $traces = $holiday->traces;

        // Assert
        $this->assertCount(1, $traces);

        $this->assertTrue($traces->contains($trace));

        $this->assertEquals($trace->traceable_id, $holiday->id);
        $this->assertEquals($trace->terminal_id, $holiday->terminal_id);
    }

    public function test_it_returns_linkable_traces(): void
    {
        // Arrange
        $holiday = Holiday::factory()->create();
        $trace = Trace::factory()->for($holiday->terminal)->create([
            'traceable_type' => Holiday::class,
            'traceable_id' => $holiday->id,
        ]);

        $holiday->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $holiday->links;

        // Assert
        $this->assertCount(1, $linkableTraces);

        $this->assertTrue($linkableTraces->contains($trace));

        $this->assertEquals($trace->traceable_id, $holiday->id);
        $this->assertEquals($trace->terminal_id, $holiday->terminal_id);
    }

    public function test_it_returns_linkable_traces_with_link_type(): void
    {
        // Arrange
        $holiday = Holiday::factory()->create();
        $trace = Trace::factory()->for($holiday->terminal)->create([
            'traceable_type' => Holiday::class,
            'traceable_id' => $holiday->id,
        ]);

        $holiday->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $holiday->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertEquals(TraceLinkType::Related->value, $linkableTraces->first()->pivot->trace_link_type);

        $this->assertTrue($linkableTraces->contains($trace));

        $this->assertEquals($trace->traceable_id, $holiday->id);
        $this->assertEquals($trace->terminal_id, $holiday->terminal_id);
    }

    public function test_it_returns_linked_attachments(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();

        $holiday = Holiday::factory()->for($terminal)->create();

        $attachment = Attachment::factory()->create([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
            'attachable_id' => $holiday->id,
            'attachable_type' => Holiday::class,
        ]);

        // Act
        $attachments = $holiday->attachments;

        // Assert
        $this->assertCount(1, $attachments);
        $this->assertTrue($attachments->contains($attachment));
    }
}
