<?php

namespace App;

enum IncidentType: string
{
    case Incident = 'incident';
    case Maintenance = 'maintenance';
    case Outage = 'outage';
    case Degradation = 'degradation';
    case Performance = 'performance';
    case Security = 'security';
    case Compliance = 'compliance';
    case Other = 'other';

    public static function default(): IncidentType
    {
        return self::Incident;
    }
}
