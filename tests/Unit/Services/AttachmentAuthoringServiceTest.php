<?php

namespace Tests\Unit\Services;

use App\Models\Attachment;
use App\Models\Session;
use App\Models\Task;
use App\Models\Terminal;
use App\Services\AttachmentAuthoringService;
use Tests\TestCase;

class AttachmentAuthoringServiceTest extends TestCase
{
    public function test_it_can_attach_an_attachment_to_attachable_model()
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $task = Task::factory()->for($terminal)->create();
        $attachmentManagerService = new AttachmentAuthoringService($terminal);

        // Act
        $attachment = $attachmentManagerService->createAttachment($task, [
            'filename' => 'test.pdf',
            'path' => 'attachments/test.pdf',
        ]);

        // Assert
        $this->assertDatabaseHas('attachments', [
            'id' => $attachment->id,
            'attachable_type' => Task::class,
            'attachable_id' => $task->id,
            'session_id' => $session->id,
            'filename' => 'test.pdf',
            'path' => 'attachments/test.pdf',
        ]);

        $this->assertEquals($attachment->attachable->id, $task->id);
        $this->assertInstanceOf(Task::class, $attachment->attachable);
    }

    public function test_it_can_archive_an_attachment()
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $attachment = Attachment::factory()->for($terminal)->create([
            'session_id' => $session->id,
        ]);
        $attachmentManagerService = new AttachmentAuthoringService($terminal);

        // Act
        $attachmentManagerService->archiveAttachment($attachment);

        // Assert
        $this->assertSoftDeleted('attachments', [
            'id' => $attachment->id,
        ]);
    }
}
