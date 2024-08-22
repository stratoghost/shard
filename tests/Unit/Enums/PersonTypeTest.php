<?php

namespace Tests\Unit\Enums;

use App\PersonType;
use Tests\TestCase;

class PersonTypeTest extends TestCase
{
    public function test_it_returns_default_person_type(): void
    {
        // Assert
        $this->assertEquals(PersonType::TeamMember, PersonType::default());
    }

    public function test_it_returns_labels(): void
    {
        // Assert
        $this->assertEquals('Team member', PersonType::TeamMember->label());
        $this->assertEquals('Manager', PersonType::Manager->label());
        $this->assertEquals('Director', PersonType::Director->label());
        $this->assertEquals('Recruiter', PersonType::Recruiter->label());
        $this->assertEquals('Candidate', PersonType::Candidate->label());
    }
}
