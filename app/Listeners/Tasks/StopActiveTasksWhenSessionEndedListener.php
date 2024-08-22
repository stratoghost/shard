<?php

namespace App\Listeners\Tasks;

use App\Events\Sessions\SessionEndedEvent;
use App\TaskStateType;

class StopActiveTasksWhenSessionEndedListener
{
    public function __construct() {}

    public function handle(SessionEndedEvent $event): void
    {
        $session = $event->session;
        $terminal = $session->terminal;

        $terminal->tasks()->where('state', TaskStateType::Started)->update(['state' => TaskStateType::Stopped]);
    }
}
