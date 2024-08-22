<?php

namespace Tests\Unit\Events\Sessions;

use App\Events\Sessions\SessionEndedEvent;
use App\Models\Session;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SessionEndedEventTest extends TestCase
{
    public function test_it_can_be_dispatched(): void
    {
        // Fake
        Event::fake([
            SessionEndedEvent::class,
        ]);

        // Arrange
        $session = Session::factory()->create([
            'started_at' => now()->subMinutes(30),
            'ended_at' => now(),
        ]);

        SessionEndedEvent::dispatch($session);

        // Assert
        Event::assertDispatched(SessionEndedEvent::class, function ($event) use ($session) {
            return $event->session->is($session);
        });
    }
}
