<?php

namespace App;

enum TraceType: string
{
    // Events
    case Event = 'event';
    case Alert = 'alert';
    case Normal = 'normal';
    case Recall = 'recall';
    case System = 'system';

    // Interactions
    case Communication = 'communication';
    case Instruction = 'instruction';
    case Outcome = 'outcome';

    public static function default(): TraceType
    {
        return self::Normal;
    }

    public static function loggingTypes(): array
    {
        return [
            self::Event,
            self::Alert,
            self::Normal,
            self::Recall,
        ];
    }

    public static function interactionTypes(): array
    {
        return [
            self::Communication,
            self::Instruction,
            self::Outcome,
        ];
    }

    public static function systemType(): TraceType
    {
        return self::System;
    }
}
