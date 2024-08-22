<?php

namespace Tests\Unit\Services;

use App\Exceptions\Common\DuplicateModelException;
use App\Exceptions\Common\ModelNotTrashedException;
use App\Exceptions\People\PersonAlreadyArchivedException;
use App\Models\Person;
use App\Models\Terminal;
use App\PersonType;
use App\Services\PersonManagerService;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PersonManagerServiceTest extends TestCase
{
    use RefreshDatabase;

    public static function providesDuplicatePersonData(): array
    {
        return [
            [
                [
                    'first_name' => 'John',
                    'email' => 'john.smith@example.com',
                    'type' => PersonType::TeamMember,
                ],
            ],
            [
                [
                    'first_name' => 'Jane',
                    'email' => null,
                    'type' => PersonType::TeamMember,
                ],
            ],
        ];
    }

    public function test_it_creates_a_person_for_a_terminal(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $faker = Factory::create();
        $personManagerService = new PersonManagerService($terminal);

        $data = [
            'first_name' => $faker->firstName(),
            'type' => PersonType::TeamMember,
            'terminal_id' => $terminal->id,
        ];

        // Act
        $person = $personManagerService->createPerson($data);

        // Assert
        $this->assertTrue($person->exists);
        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'first_name' => $person->first_name,
            'terminal_id' => $person->terminal_id,
        ]);
    }

    #[dataProvider('providesDuplicatePersonData')]
    public function test_it_cannot_create_a_duplicate_person(array $data): void
    {
        // Expect
        $this->expectException(DuplicateModelException::class);

        // Arrange
        $terminal = Terminal::factory()->create();
        $personManagerService = new PersonManagerService($terminal);
        $person = Person::factory()->for($terminal)->create($data);

        // Act
        $personManagerService->createPerson($person->attributesToArray());
    }

    public function test_it_can_modify_a_person_for_terminal(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $person = Person::factory()->create(['first_name' => 'John', 'terminal_id' => $terminal->id]);
        $personManagerService = new PersonManagerService($terminal);

        // Act
        $person = $personManagerService->modifyPerson($person, ['first_name' => 'Jane']);

        // Assert
        $this->assertInstanceOf(Person::class, $person);
        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'first_name' => 'Jane',
        ]);
    }

    public function test_it_cannot_modify_a_person_to_become_a_duplicate(): void
    {
        // Expect
        $this->expectException(DuplicateModelException::class);

        // Arrange
        $terminal = Terminal::factory()->create();
        $personManagerService = new PersonManagerService($terminal);
        $person1 = Person::factory()->create(['first_name' => 'John', 'terminal_id' => $terminal->id]);
        Person::factory()->create(['first_name' => 'Jane', 'terminal_id' => $terminal->id]);

        // Act
        $personManagerService->modifyPerson($person1, ['first_name' => 'Jane']);
    }

    public function test_it_can_archive_a_person(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $person = Person::factory()->create(['terminal_id' => $terminal->id]);
        $personManagerService = new PersonManagerService($terminal);

        // Act
        $personManagerService->archivePerson($person);

        // Assert
        $this->assertSoftDeleted('people', ['id' => $person->id]);
    }

    public function test_it_cannot_archive_a_person_already_archived(): void
    {
        // Expect
        $this->expectException(PersonAlreadyArchivedException::class);

        // Arrange
        $terminal = Terminal::factory()->create();
        $person = Person::factory()->create(['terminal_id' => $terminal->id, 'deleted_at' => now()]);
        $personManagerService = new PersonManagerService($terminal);

        // Act
        $personManagerService->archivePerson($person);
    }

    public function test_it_can_restore_a_person_from_archive(): void
    {
        // Arrange
        $terminal = Terminal::factory()->create();
        $person = Person::factory()->create(['terminal_id' => $terminal->id, 'deleted_at' => now()]);
        $personManagerService = new PersonManagerService($terminal);

        // Act
        $personManagerService->restorePerson($person);

        // Assert
        $this->assertDatabaseHas('people', ['id' => $person->id, 'deleted_at' => null]);
    }

    public function test_it_cannot_restore_a_person_not_archived(): void
    {
        // Expect
        $this->expectException(ModelNotTrashedException::class);

        // Arrange
        $terminal = Terminal::factory()->create();
        $person = Person::factory()->create(['terminal_id' => $terminal->id]);
        $personManagerService = new PersonManagerService($terminal);

        // Act
        $personManagerService->restorePerson($person);
    }
}
