<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use Exception;

class ConflictException extends Exception
{
    public function __construct(string $message = 'Conflict', int $code = 409)
    {
        parent::__construct($message, $code);
    }
}
