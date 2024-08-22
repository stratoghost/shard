<?php

namespace App\Exceptions\Collections;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class CollectionAlreadyExistsException extends HttpException
{
    private const STATUS_CODE = 409;

    private const DEFAULT_MESSAGE = 'A collection with the same name already exists for this terminal';

    public function __construct(?string $message = null, ?Throwable $previous = null, array $headers = [], $code = 0)
    {
        $message = $message ?? self::DEFAULT_MESSAGE;

        parent::__construct(self::STATUS_CODE, $message, $previous, $headers, $code);
    }
}
