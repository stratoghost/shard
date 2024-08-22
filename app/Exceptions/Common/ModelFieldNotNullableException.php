<?php

namespace App\Exceptions\Common;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class ModelFieldNotNullableException extends HttpException
{
    private const STATUS_CODE = 400;

    private const DEFAULT_MESSAGE = 'Attempting to nullify a model field which is not nullable';

    public function __construct(?string $message = null, ?Throwable $previous = null, array $headers = [], $code = 0)
    {
        $message = $message ?? self::DEFAULT_MESSAGE;

        parent::__construct(self::STATUS_CODE, $message, $previous, $headers, $code);
    }
}
