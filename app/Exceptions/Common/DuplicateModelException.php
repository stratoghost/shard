<?php

namespace App\Exceptions\Common;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class DuplicateModelException extends HttpException
{
    private const STATUS_CODE = 409;

    private const DEFAULT_MESSAGE = 'Cannot create a model which would result in a duplicate entry';

    public function __construct(?string $message = null, ?Throwable $previous = null, array $headers = [], $code = 0)
    {
        $message = $message ?? self::DEFAULT_MESSAGE;

        parent::__construct(self::STATUS_CODE, $message, $previous, $headers, $code);
    }
}
