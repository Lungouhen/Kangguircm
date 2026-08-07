<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;

class CsrfMiddleware
{
    public function handle(): bool
    {
        return Csrf::middleware();
    }
}
