<?php

namespace App\Exceptions\Terminals;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class EmptyTerminalIdentifierException extends HttpException
{
    private const STATUS_CODE = 400;

    private const DEFAULT_MESSAGE = 'An empty terminal identifier was provided';

    public function __construct(?string $message = null, ?Throwable $previous = null, array $additionalHeaders = [], int $errorCode = 0)
    {
        $message = $message ?? self::DEFAULT_MESSAGE;

        parent::__construct(self::STATUS_CODE, $message, $previous, $additionalHeaders, $errorCode);
    }
}
