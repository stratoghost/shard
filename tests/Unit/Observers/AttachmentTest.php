<?php

namespace Tests\Unit\Observers;

use App\Models\Attachment;
use App\Models\Session;
use App\Models\Terminal;
use Tests\TestCase;

class AttachmentTest extends TestCase
{
    public function test_it_populates_label(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $terminalPrefix = explode('_', $terminal->identifier)[0];
        Attachment::factory(3)->for($terminal)->create(['session_id' => $session->id]);
        $attachment = Attachment::factory()->for($terminal)->create(['session_id' => $session->id]);

        // Act
        $attachment->save();
        $attachment->refresh();

        // Assert
        $this->assertTrue($attachment->terminal->is($terminal));
        $this->assertEquals($terminalPrefix.'/'.$session->id.'-4/'.date('Ymd'), $attachment->label);

        $this->assertTrue($attachment->terminal->is($terminal));
        $this->assertTrue($attachment->session->is($session));
    }

    public function test_it_sets_attachable_to_session_when_not_provided(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $attachment = Attachment::factory()->for($terminal)->make(['session_id' => $session->id]);

        // Act
        $attachment->save();

        // Assert
        $this->assertEquals($session->id, $attachment->session_id);
        $this->assertInstanceOf(Session::class, $attachment->attachable);

        $this->assertTrue($attachment->terminal->is($terminal));
        $this->assertTrue($attachment->session->is($session));
    }

    public function test_it_sets_session_id_to_latest_terminal_session_when_not_provided(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        Session::factory(3)->for($terminal)->create(['started_at' => now()->subDays(3), 'ended_at' => now()->subDays(2)]);
        $session = Session::factory()->for($terminal)->create();
        $attachment = Attachment::factory()->for($terminal)->make([
            'session_id' => null,
        ]);

        // Act
        $attachment->save();

        // Assert
        $this->assertTrue($attachment->terminal->is($terminal));
        $this->assertTrue($attachment->session->is($session));
    }
}
