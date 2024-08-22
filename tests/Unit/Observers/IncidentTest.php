<?php

namespace Tests\Unit\Observers;

use App\IncidentGradeType;
use App\IncidentType;
use App\Models\Incident;
use Tests\TestCase;

class IncidentTest extends TestCase
{
    public function test_it_populates_started_at_field_when_creating(): void
    {
        // Arrange
        $incident = Incident::factory()->make();

        // Act
        $incident->save();

        // Assert
        $this->assertNotNull($incident->started_at);
    }

    public function test_it_calculates_time_to_resolution_when_resolved(): void
    {
        // Arrange
        $incident = Incident::factory()->create(['started_at' => now()->subHour()]);

        // Act
        $incident->resolved_at = now();
        $incident->save();
        $incident->refresh();

        // Assert
        $this->assertNotNull($incident->resolved_at);
        $this->assertEquals(60, $incident->time_to_resolution);
    }

    public function test_it_sets_default_incident_type_when_creating(): void
    {
        // Arrange
        $incident = Incident::factory()->make();

        // Act
        $incident->save();

        // Assert
        $this->assertEquals(IncidentType::Incident, $incident->type);
    }

    public function test_it_sets_default_incident_grade_when_creating(): void
    {
        // Arrange
        $incident = Incident::factory()->make();

        // Act
        $incident->save();

        // Assert
        $this->assertEquals(IncidentGradeType::default(), $incident->grade);
    }
}
