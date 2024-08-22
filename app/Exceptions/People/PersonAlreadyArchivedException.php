<?php

namespace App\Exceptions\People;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class PersonAlreadyArchivedException extends HttpException
{
    private const STATUS_CODE = 404;

    private const DEFAULT_MESSAGE = 'The person has already been archived';

    public function __construct(?string $message = null, ?Throwable $previous = null, array $headers = [], $code = 0)
    {
        $message = $message ?? self::DEFAULT_MESSAGE;

        parent::__construct(self::STATUS_CODE, $message, $previous, $headers, $code);
    }
}
