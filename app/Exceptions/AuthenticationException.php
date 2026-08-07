<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Authentication exception for unauthorized access attempts.
 */
class AuthenticationException extends RuntimeException
{
    public function __construct(string $message = 'Unauthorized')
    {
        parent::__construct($message, 401);
    }
}
