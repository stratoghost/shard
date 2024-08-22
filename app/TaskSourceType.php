<?php

namespace App;

enum TaskSourceType: string
{
    case Jira = 'jira';
    case Discord = 'discord';
    case Email = 'email';
    case Phone = 'phone';
    case Slack = 'slack';
    case Teams = 'teams';
    case Internal = 'internal';
    case Approached = 'approached';

    public static function default(): TaskSourceType
    {
        return TaskSourceType::Internal;
    }
}
