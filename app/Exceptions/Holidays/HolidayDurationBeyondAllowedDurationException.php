<?php

namespace App\Exceptions\Holidays;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class HolidayDurationBeyondAllowedDurationException extends HttpException
{
    private const STATUS_CODE = 422;

    private const DEFAULT_MESSAGE = 'The holiday duration is beyond the allowed duration';

    public function __construct($message = null, ?Throwable $previous = null, array $headers = [], $code = 0)
    {
        $message = $message ?? self::DEFAULT_MESSAGE;

        parent::__construct(self::STATUS_CODE, $message, $previous, $headers, $code);
    }
}
