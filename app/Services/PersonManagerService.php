<?php

namespace App\Services;

use App\Exceptions\Common\DuplicateModelException;
use App\Exceptions\Common\ModelNotTrashedException;
use App\Exceptions\People\PersonAlreadyArchivedException;
use App\Models\Person;
use App\Models\Terminal;

readonly class PersonManagerService
{
    public function __construct(protected Terminal $terminal) {}

    public function archivePerson(Person $person): void
    {
        if ($person->trashed()) {
            throw new PersonAlreadyArchivedException;
        }

        $person->delete();
    }

    public function createPerson(array $data): Person
    {
        $similarPerson = $this->terminal->people()
            ->where('first_name', $data['first_name'] ?? null)
            ->where('email', $data['email'] ?? null)
            ->where('type', $data['type'] ?? null)
            ->first();

        if (! is_null($similarPerson)) {
            throw new DuplicateModelException;
        }

        return $this->terminal->people()->create($data);
    }

    public function modifyPerson(Person $person, array $attributes): Person
    {
        $similarPerson = $this->terminal->people()
            ->where('first_name', $attributes['first_name'] ?? null)
            ->where('email', $attributes['email'] ?? null)
            ->first();

        if (! is_null($similarPerson)) {
            throw new DuplicateModelException;
        }

        $person->update($attributes);

        return $person;
    }

    public function restorePerson(Person $person)
    {
        if (! $person->trashed()) {
            throw new ModelNotTrashedException;
        }

        $person->restore();
    }
}
