<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Authorization exception for insufficient permissions.
 */
class AuthorizationException extends RuntimeException
{
    public function __construct(string $message = 'Forbidden')
    {
        parent::__construct($message, 403);
    }
}
