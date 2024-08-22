<?php

namespace Tests\Unit\Models;

use App\Models\Attachment;
use App\Models\Session;
use App\Models\Terminal;
use App\Models\Trace;
use App\TraceLinkType;
use Tests\TestCase;

class AttachmentTest extends TestCase
{
    public function test_it_can_create_a_model(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $attachment = Attachment::factory()->for($terminal)->make([
            'session_id' => $session->id,
        ]);

        // Act
        $attachment->save();

        // Assert
        $this->assertTrue($attachment->exists);
        $this->assertDatabaseHas('attachments', [
            'id' => $attachment->id,
            'session_id' => $session->id,
            'terminal_id' => $terminal->id,
        ]);
    }

    public function test_it_returns_a_terminal(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $attachment = Attachment::factory()->for($terminal)->create([
            'session_id' => $session->id,
        ]);

        // Act
        $terminal = $attachment->terminal;

        // Assert
        $this->assertEquals($attachment->terminal_id, $terminal->id);
    }

    public function test_it_returns_a_session(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $attachment = Attachment::factory()->for($terminal)->create([
            'session_id' => $session->id,
        ]);

        // Act
        $session = $attachment->session;

        // Assert
        $this->assertEquals($attachment->session_id, $session->id);
        $this->assertEquals($attachment->terminal_id, $session->terminal_id);
    }

    public function test_it_returns_attachable_relation(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $attachment = Attachment::factory()->for($terminal)->create([
            'session_id' => $session->id,
        ]);

        // Act
        $attachable = $attachment->attachable;

        // Assert
        $this->assertEquals($attachment->attachable_id, $attachable->id);
        $this->assertInstanceOf(Session::class, $attachable);
        $this->assertEquals($attachment->session_id, $attachable->id);
        $this->assertEquals($attachment->terminal_id, $attachable->terminal_id);
    }

    public function test_it_returns_traces()
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $attachment = Attachment::factory()->for($terminal)->create([
            'session_id' => $session->id,
        ]);

        $trace = Trace::factory()->create([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
            'traceable_type' => Attachment::class,
            'traceable_id' => $attachment->id,
        ]);

        // Act
        $traces = $attachment->traces;

        // Assert
        $this->assertCount(1, $traces);
        $this->assertEquals($trace->id, $traces->first()->id);
        $this->assertEquals($trace->terminal_id, $traces->first()->terminal_id);
        $this->assertEquals($trace->session_id, $traces->first()->session_id);
    }

    public function test_it_returns_linkable_traces(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $attachment = Attachment::factory()->for($terminal)->create([
            'session_id' => $session->id,
        ]);

        $trace = Trace::factory()->create([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
            'traceable_type' => Attachment::class,
            'traceable_id' => $attachment->id,
        ]);

        $attachment->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $attachment->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertTrue($linkableTraces->contains($trace));
        $this->assertEquals($trace->terminal_id, $linkableTraces->first()->terminal_id);
        $this->assertEquals($trace->session_id, $linkableTraces->first()->session_id);
    }

    public function test_it_returns_linkable_traces_with_link_type(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $attachment = Attachment::factory()->for($terminal)->create([
            'session_id' => $session->id,
        ]);

        $trace = Trace::factory()->create([
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
            'traceable_type' => Attachment::class,
            'traceable_id' => $attachment->id,
        ]);

        $attachment->links()->attach($trace, [
            'created_at' => now(),
            'updated_at' => now(),
            'trace_link_type' => TraceLinkType::Related,
        ]);

        // Act
        $linkableTraces = $attachment->links;

        // Assert
        $this->assertCount(1, $linkableTraces);
        $this->assertEquals(TraceLinkType::Related->value, $linkableTraces->first()->pivot->trace_link_type);

        $this->assertTrue($linkableTraces->contains($trace));

        $this->assertEquals($trace->terminal_id, $linkableTraces->first()->terminal_id);
        $this->assertEquals($trace->session_id, $linkableTraces->first()->session_id);
    }
}
