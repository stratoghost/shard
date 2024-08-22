<?php

namespace Tests;

use App\Models\Absence;
use App\Models\Attachment;
use App\Models\Collection;
use App\Models\Holiday;
use App\Models\Incident;
use App\Models\Person;
use App\Models\Session;
use App\Models\Snapshot;
use App\Models\Task;
use App\Models\Terminal;
use App\Models\TimeClock;
use App\Models\Trace;
use App\Models\User;
use App\Models\Worklog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Terminal::factory()->create();
        Session::factory()->create();
        Absence::factory()->create();
        Attachment::factory()->create();
        Collection::factory()->create();
        Holiday::factory()->create();
        Incident::factory()->create();
        Person::factory()->create();
        Snapshot::factory()->create();
        Task::factory()->create();
        TimeClock::factory()->create();
        Trace::factory()->create();
        User::factory()->create();
        Worklog::factory()->create();
    }
}
