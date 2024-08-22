<?php

namespace App\Exceptions\Traces;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class TraceContentCannotBeEmptyException extends HttpException
{
    private const STATUS_CODE = 400;

    private const DEFAULT_MESSAGE = 'The content of a trace cannot be empty';

    public function __construct($message = null, ?Throwable $previous = null, array $headers = [], $code = 0)
    {
        $message = $message ?? self::DEFAULT_MESSAGE;

        parent::__construct(self::STATUS_CODE, $message, $previous, $headers, $code);
    }
}
