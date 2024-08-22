<?php

namespace App\Exceptions\Common;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class ModelNotModifiableException extends HttpException
{
    private const STATUS_CODE = 400;

    private const DEFAULT_MESSAGE = 'Attempting to modify a model which is in a readonly state';

    public function __construct(?string $message = null, ?Throwable $previous = null, array $headers = [], $code = 0)
    {
        $message = $message ?? self::DEFAULT_MESSAGE;

        parent::__construct(self::STATUS_CODE, $message, $previous, $headers, $code);
    }
}
