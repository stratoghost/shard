<?php

namespace App\Exceptions\Incidents;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class IncidentAlreadyHasResolutionException extends HttpException
{
    private const STATUS_CODE = 409;

    private const DEFAULT_MESSAGE = 'The incident has already been marked as resolved';

    public function __construct(?string $message = null, ?Throwable $previous = null, array $headers = [], $code = 0)
    {
        $message = $message ?? self::DEFAULT_MESSAGE;

        parent::__construct(self::STATUS_CODE, $message, $previous, $headers, $code);
    }
}
