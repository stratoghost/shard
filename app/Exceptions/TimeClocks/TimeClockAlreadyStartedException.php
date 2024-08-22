<?php

namespace App\Exceptions\TimeClocks;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class TimeClockAlreadyStartedException extends HttpException
{
    private const STATUS_CODE = 409;

    private const DEFAULT_MESSAGE = 'A time clock has already been started for this session.';

    public function __construct(?string $message = null, ?Throwable $previous = null, array $headers = [], $code = 0)
    {
        $message = $message ?? self::DEFAULT_MESSAGE;

        parent::__construct(self::STATUS_CODE, $message, $previous, $headers, $code);
    }
}
