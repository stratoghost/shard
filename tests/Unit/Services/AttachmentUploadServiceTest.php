<?php

namespace Tests\Unit\Services;

use App\Models\Session;
use App\Models\Task;
use App\Models\Terminal;
use App\Services\AttachmentUploadService;
use Illuminate\Http\UploadedFile;
use Storage;
use Tests\TestCase;

class AttachmentUploadServiceTest extends TestCase
{
    public function test_it_uploads_attachment()
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $session = Session::factory()->for($terminal)->create();
        $task = Task::factory()->for($terminal)->create();

        $service = new AttachmentUploadService($terminal);

        $file = UploadedFile::fake()->image('avatar.jpg');

        // Act
        $attachment = $service->createAttachment($task, $file);

        // Assert
        $expectedPath = 'usercontent/'.hash('sha256', $terminal->id.'_root_'.$terminal->user_id).'/'.hash('sha256', $task->getTable().'_'.$terminal->getKey()).'/'.$attachment->filename;

        $this->assertNotNull($attachment);

        $this->assertDatabaseHas('attachments', [
            'id' => $attachment->id,
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
            'attachable_id' => $task->id,
            'attachable_type' => Task::class,
            'filename' => $file->hashName(),
            'path' => $expectedPath,
        ]);

        Storage::disk('local')->assertExists($expectedPath);
    }
}
