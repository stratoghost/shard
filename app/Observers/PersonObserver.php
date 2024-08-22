<?php

namespace App\Observers;

use App\Models\Person;
use App\PersonType;

class PersonObserver
{
    public function creating(Person $person): void
    {
        if (is_null($person->type)) {
            $person->type = PersonType::default();
        }
    }
}
