<?php

namespace App;

enum PersonType: string
{
    case TeamMember = 'team_member';
    case Manager = 'manager';
    case Director = 'director';
    case Recruiter = 'recruiter';
    case Candidate = 'candidate';

    public static function default(): PersonType
    {
        return self::TeamMember;
    }

    public function label(): string
    {
        return match ($this) {
            self::TeamMember => 'Team member',
            self::Manager => 'Manager',
            self::Director => 'Director',
            self::Recruiter => 'Recruiter',
            self::Candidate => 'Candidate',
        };
    }
}
