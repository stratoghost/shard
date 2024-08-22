<?php

namespace App\Exceptions\Sessions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class SessionAlreadyStartedException extends HttpException
{
    private const STATUS_CODE = 409;

    private const DEFAULT_MESSAGE = 'An session has already been started for this terminal';

    public function __construct(?string $message = null, ?Throwable $previous = null, array $headers = [], $code = 0)
    {
        $message = $message ?? self::DEFAULT_MESSAGE;

        parent::__construct(self::STATUS_CODE, $message, $previous, $headers, $code);
    }
}
