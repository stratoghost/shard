<?php

namespace Tests\Unit\Services;

use App\Models\Incident;
use App\Models\Terminal;
use App\Services\InitiateIncidentService;
use Tests\TestCase;

class InitiateIncidentServiceTest extends TestCase
{
    public function test_it_creates_an_incident(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $incident = Incident::factory()->for($terminal)->make();
        $incidentManagerService = new InitiateIncidentService($terminal);

        // Act
        $incident = $incidentManagerService->createIncident($incident->attributesToArray());

        // Assert
        $this->assertDatabaseHas('incidents', ['id' => $incident->id, 'terminal_id' => $terminal->id, 'started_at' => now()]);
    }

    public function test_it_can_create_an_incident_when_an_active_incident_exists(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        Incident::factory()->for($terminal)->create();
        $incidentManagerService = new InitiateIncidentService($terminal);

        $count = Incident::count();

        // Act
        $incidentManagerService->createIncident(Incident::factory()->for($terminal)->make()->attributesToArray());

        // Assert
        $this->assertDatabaseCount('incidents', $count + 1);
    }
}
