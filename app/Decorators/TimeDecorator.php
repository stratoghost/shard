<?php

namespace App\Decorators;

use Carbon\CarbonInterface;

readonly class TimeDecorator
{
    public function remainingMinutesToFormattedTime(int $remainingMinutes): string
    {
        $hours = floor($remainingMinutes / 60);
        $minutes = $remainingMinutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public function remainingMinutesFromExpectedMinutes(int $expectedMinutes, int $actualMinutes): string
    {
        $remainingMinutes = $expectedMinutes - $actualMinutes;

        if ($remainingMinutes < 0) {
            $hours = floor(abs($remainingMinutes) / 60);
            $minutes = abs($remainingMinutes) % 60;

            return sprintf('-%02d:%02d', $hours, $minutes);
        } else {
            $hours = floor($remainingMinutes / 60);
            $minutes = $remainingMinutes % 60;

            return sprintf('%02d:%02d', $hours, $minutes);
        }

    }

    public function expectedEndTimeFromRemainingMinutes(int $expectedMinutes, int $actualMinutes): string
    {

        $remainingMinutes = $expectedMinutes - $actualMinutes;

        $expectedEndTime = now()->addMinutes($remainingMinutes);

        return $expectedEndTime->format('H:i');
    }

    public function asHoursAndMinutes(CarbonInterface $carbonInterface): string
    {
        return $carbonInterface->format('H:i');
    }

    public function expectedEndTimeFromExpectedMinutes(CarbonInterface $carbonInterface, int $expectedMinutes): string
    {
        $expectedEndTime = $carbonInterface->addMinutes($expectedMinutes);

        return $expectedEndTime->format('H:i');
    }
}
