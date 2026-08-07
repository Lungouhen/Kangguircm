<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Validation exception thrown when input data fails validation.
 */
class ValidationException extends RuntimeException
{
    /** @var array<string, list<string>> */
    private array $errors;

    /**
     * @param array<string, list<string>> $errors
     */
    public function __construct(array $errors, string $message = 'Validation failed')
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    /**
     * @return array<string, list<string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @return list<string>
     */
    public function flatErrors(): array
    {
        return array_merge(...array_values($this->errors));
    }
}
