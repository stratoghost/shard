<?php

namespace Tests\Unit\Services;

use App\Exceptions\Common\ModelFieldGuardedException;
use App\Exceptions\Common\ModelFieldNotNullableException;
use App\Exceptions\Common\ModelNotModifiableException;
use App\Exceptions\Incidents\IncidentAlreadyHasResolutionException;
use App\IncidentGradeType;
use App\Models\Incident;
use App\Models\Terminal;
use App\Services\UpdateActiveIncidentService;
use Tests\TestCase;

class UpdateActiveIncidentServiceTest extends TestCase
{
    public function test_it_can_mark_an_incident_as_resolved(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $incident = Incident::factory()->for($terminal)->create();
        $incidentManagerService = new UpdateActiveIncidentService($terminal);

        // Act
        $incidentManagerService->resolveIncident($incident);

        // Assert
        $this->assertNotNull($incident->resolved_at);
    }

    public function test_it_cannot_resolve_an_incident_already_resolved(): void
    {
        // Expect
        $this->expectException(IncidentAlreadyHasResolutionException::class);

        // Arrange
        $terminal = Terminal::factory()->create();
        $incident = Incident::factory()->for($terminal)->create(['resolved_at' => now()]);
        $incidentManagerService = new UpdateActiveIncidentService($terminal);

        // Act
        $incidentManagerService->resolveIncident($incident);
    }

    public function test_it_can_update_the_incident_grade(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $incident = Incident::factory()->for($terminal)->create();
        $incidentManagerService = new UpdateActiveIncidentService($terminal);

        // Act
        $incidentManagerService->updateIncident($incident, ['grade' => IncidentGradeType::Critical]);

        // Assert
        $this->assertDatabaseHas('incidents', ['id' => $incident->id, 'grade' => IncidentGradeType::Critical]);
    }

    public function test_it_can_update_description_of_incident(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $incident = Incident::factory()->for($terminal)->create();
        $incidentManagerService = new UpdateActiveIncidentService($terminal);

        // Act
        $incidentManagerService->updateIncident($incident, ['description' => 'New description']);

        // Assert
        $this->assertDatabaseHas('incidents', ['id' => $incident->id, 'description' => 'New description']);
    }

    public function test_it_can_mark_an_incident_as_ended(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $incident = Incident::factory()->for($terminal)->create();
        $incidentManagerService = new UpdateActiveIncidentService($terminal);

        // Act
        $incidentManagerService->closeIncident($incident);

        // Assert
        $this->assertNotNull($incident->ended_at);
    }

    public function test_it_cannot_change_protected_fields(): void
    {
        // Expect
        $this->expectException(ModelFieldGuardedException::class);

        // Arrange
        $terminal = Terminal::factory()->create();
        $incident = Incident::factory()->for($terminal)->create();
        $incidentManagerService = new UpdateActiveIncidentService($terminal);

        // Act
        $incidentManagerService->updateIncident($incident, ['started_at' => now(), 'resolved_at' => now(), 'ended_at' => now()]);
    }

    public function test_it_cannot_nullify_protected_fields(): void
    {
        // Expect
        $this->expectException(ModelFieldNotNullableException::class);

        // Arrange
        $terminal = Terminal::factory()->create();
        $incident = Incident::factory()->for($terminal)->create();
        $incidentManagerService = new UpdateActiveIncidentService($terminal);

        // Act
        $incidentManagerService->updateIncident($incident, ['grade' => null]);
    }

    public function test_it_cannot_change_an_incident_when_ended(): void
    {
        // Expect
        $this->expectException(ModelNotModifiableException::class);

        // Arrange
        $terminal = Terminal::factory()->create();
        $incident = Incident::factory()->for($terminal)->create(['ended_at' => now()]);
        $incidentManagerService = new UpdateActiveIncidentService($terminal);

        // Act
        $incidentManagerService->updateIncident($incident, ['grade' => IncidentGradeType::Critical]);
    }
}
