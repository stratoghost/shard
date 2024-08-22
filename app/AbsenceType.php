<?php

namespace App;

enum AbsenceType: string
{
    case MentalHealth = 'mental_health';
    case PhysicalHealth = 'physical_health';
    case FamilyEmergency = 'family_emergency';
    case PersonalEmergency = 'personal_emergency';
    case Bereavement = 'bereavement';
    case JuryDuty = 'jury_duty';
    case UnpaidLeave = 'unpaid_leave';

    public static function default(): AbsenceType
    {
        return self::UnpaidLeave;
    }
}
