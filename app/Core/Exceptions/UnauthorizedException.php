<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use Exception;

class UnauthorizedException extends Exception
{
    public function __construct(string $message = 'Unauthorized', int $code = 401)
    {
        parent::__construct($message, $code);
    }
}
