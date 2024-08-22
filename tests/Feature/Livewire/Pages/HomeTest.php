<?php

namespace Tests\Feature\Livewire\Pages;

use App\Models\Task;
use App\Models\User;
use App\Services\SessionInstanceService;
use App\SessionType;
use App\TraceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Volt\Volt;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_gets_the_active_session()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $this->actingAs($user);

        // Act
        resolve(SessionInstanceService::class, [
            'terminal' => $terminal,
        ])->startSession(SessionType::StandardDuration);

        $session = $terminal->sessions()->first();

        // Assert
        Volt::test('pages.home')
            ->assertSet('session', $session)
            ->assertHasNoErrors();
    }

    public function test_it_gets_the_current_terminal()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $this->actingAs($user);

        // Act
        $terminal = $terminal->fresh();

        // Assert
        Volt::test('pages.home')
            ->assertSet('terminal', $terminal)
            ->assertHasNoErrors();
    }

    public function test_it_can_create_a_trace_entry()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $session = resolve(SessionInstanceService::class, [
            'terminal' => $terminal,
        ])->startSession(SessionType::StandardDuration);

        $this->actingAs($user);

        // Act
        $component = Volt::test('pages.home')
            ->set('content', 'lorem ipsum')
            ->call('createTrace')
            ->assertHasNoErrors();

        // Assert
        $this->assertDatabaseHas('traces', [
            'traceable_type' => get_class($session),
            'traceable_id' => $session->id,
            'content' => 'lorem ipsum',
            'type' => TraceType::default(),
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
        ]);
    }

    public function test_it_can_create_trace_entry_for_traceable_model()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $task = Task::factory()->for($terminal)->create();

        $session = resolve(SessionInstanceService::class, [
            'terminal' => $terminal,
        ])->startSession(SessionType::StandardDuration);

        $this->actingAs($user);

        // Act
        $component = Volt::test('pages.home', [
            'traceable' => $task,
        ])
            ->set('content', 'lorem ipsum')
            ->call('createTrace')
            ->assertHasNoErrors();

        // Assert
        $this->assertDatabaseHas('traces', [
            'session_id' => $session->id,
            'terminal_id' => $terminal->id,
            'traceable_type' => get_class($task),
            'traceable_id' => $task->id,
            'content' => 'lorem ipsum',
            'type' => TraceType::default(),
        ]);
    }

    public function test_it_can_create_a_trace_with_selected_type()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $session = resolve(SessionInstanceService::class, [
            'terminal' => $terminal,
        ])->startSession(SessionType::StandardDuration);

        $this->actingAs($user);

        // Act
        $component = Volt::test('pages.home')
            ->set('content', 'lorem ipsum')
            ->set('type', TraceType::Alert)
            ->call('createTrace')
            ->assertHasNoErrors();

        // Assert
        $this->assertDatabaseHas('traces', [
            'session_id' => $session->id,
            'terminal_id' => $terminal->id,
            'traceable_type' => get_class($session),
            'traceable_id' => $session->id,
            'content' => 'lorem ipsum',
            'type' => TraceType::Alert,
        ]);
    }

    public function test_it_does_not_create_trace_when_no_content_provided()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $session = resolve(SessionInstanceService::class, [
            'terminal' => $terminal,
        ])->startSession(SessionType::StandardDuration);

        $this->actingAs($user);

        // Act
        $component = Volt::test('pages.home')
            ->set('content', '')
            ->call('createTrace')
            ->assertHasErrors(['content' => 'required']);

        // Assert
        $this->assertDatabaseMissing('traces', [
            'session_id' => $session->id,
            'terminal_id' => $terminal->id,
        ]);
    }

    public function test__it_uploads_attachments_when_creating_trace()
    {
        // Arrange
        $user = User::factory()->create();
        $terminal = $user->terminals()->first();

        $session = resolve(SessionInstanceService::class, [
            'terminal' => $terminal,
        ])->startSession(SessionType::StandardDuration);

        $this->actingAs($user);

        $file = UploadedFile::fake()->image('avatar.jpg');

        // Act
        $component = Volt::test('pages.home')
            ->set('content', 'lorem ipsum')
            ->set('type', TraceType::Alert)
            ->set('files', [$file])
            ->call('createTrace')
            ->assertHasNoErrors();

        $trace = $session->traces()->where('type', TraceType::Alert)->first();

        // Assert
        $this->assertDatabaseHas('traces', [
            'session_id' => $session->id,
            'terminal_id' => $terminal->id,
            'traceable_type' => get_class($session),
            'traceable_id' => $session->id,
            'content' => 'lorem ipsum',
            'type' => TraceType::Alert,
        ]);

        $this->assertDatabaseHas('attachments', [
            'terminal_id' => $terminal->id,
            'session_id' => $session->id,
            'filename' => $file->hashName(),
            'attachable_type' => get_class($trace),
            'attachable_id' => $trace->id,
        ]);
    }
}
