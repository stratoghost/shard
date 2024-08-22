<?php

namespace App\Exceptions\Sessions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class SessionNotStartedException extends HttpException
{
    private const STATUS_CODE = 404;

    private const DEFAULT_MESSAGE = 'You must start a session before you can end it';

    public function __construct(?string $message = null, ?Throwable $previous = null, array $headers = [], $code = 0)
    {
        $message = $message ?? self::DEFAULT_MESSAGE;

        parent::__construct(self::STATUS_CODE, $message, $previous, $headers, $code);
    }
}
