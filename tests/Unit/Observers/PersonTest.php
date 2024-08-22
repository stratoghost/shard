<?php

namespace Tests\Unit\Observers;

use App\Models\Person;
use App\PersonType;
use Tests\TestCase;

class PersonTest extends TestCase
{
    public function test_it_defaults_person_type_when_none_provided(): void
    {
        // Arrange
        $person = Person::factory()->make();

        // Act
        $person->save();

        // Assert
        $this->assertEquals(PersonType::TeamMember, $person->type);
    }
}
