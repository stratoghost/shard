<?php

namespace App;

enum TaskQueueType: string
{
    case Incident = 'incident';
    case Assistance = 'assistance';
    case ServiceDesk = 'service_desk';
    case Scheduled = 'scheduled';
    case Unscheduled = 'unscheduled';

    public static function default(): TaskQueueType
    {
        return TaskQueueType::Unscheduled;
    }

    public static function labels(): array
    {
        return [
            TaskQueueType::Incident->value => 'Incident',
            TaskQueueType::Assistance->value => 'Assistance',
            TaskQueueType::ServiceDesk->value => 'Service Desk',
            TaskQueueType::Scheduled->value => 'Scheduled',
            TaskQueueType::Unscheduled->value => 'Unscheduled',
        ];
    }
}
